<?php

class GCC_Database
{

    private $table_products;
    private $table_submits;
    private $table_personas;
    private $table_questions;

    public function __construct()
    {
        global $wpdb;
        $this->table_products = $wpdb->prefix . 'gcc_products';
        $this->table_submits = $wpdb->prefix . 'gcc_submits';
        $this->table_personas = $wpdb->prefix . 'gcc_chat_persons';
        $this->table_questions = $wpdb->prefix . 'gcc_chatbot_questions';

        add_action('wp_ajax_gcc_get_products', array($this, 'get_products_ajax'));
        add_action('wp_ajax_nopriv_gcc_get_products', array($this, 'get_products_ajax'));
        add_action('wp_ajax_gcc_test_database', array($this, 'test_database_ajax'));
        add_action('wp_ajax_nopriv_gcc_test_database', array($this, 'test_database_ajax'));
        add_action('wp_ajax_gcc_get_chatbot_questions', array($this, 'get_chatbot_questions_ajax'));
        add_action('wp_ajax_nopriv_gcc_get_chatbot_questions', array($this, 'get_chatbot_questions_ajax'));
        add_action('wp_ajax_gcc_get_all_chatbot_questions', array($this, 'get_all_chatbot_questions_ajax'));
        add_action('wp_ajax_nopriv_gcc_get_all_chatbot_questions', array($this, 'get_all_chatbot_questions_ajax'));
        add_action('wp_ajax_gcc_calculate_optimal_products', array($this, 'calculate_optimal_products_ajax'));
        add_action('wp_ajax_nopriv_gcc_calculate_optimal_products', array($this, 'calculate_optimal_products_ajax'));
        add_action('wp_ajax_gcc_submit_contact', array($this, 'submit_contact_ajax'));
        add_action('wp_ajax_nopriv_gcc_submit_contact', array($this, 'submit_contact_ajax'));
    }

    public function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Products table
        $sql_products = "CREATE TABLE $this->table_products (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT '',
            article_number varchar(100) DEFAULT '',
            type enum('bar', 'ducat') NOT NULL,
            weight varchar(50) NOT NULL,
            price_net decimal(10,2) NOT NULL,
            price_gross decimal(10,2) NOT NULL,
            number_products int(11) DEFAULT 0,
            status enum('published', 'draft') DEFAULT 'draft',
            featured_image varchar(500) DEFAULT '',
            price_updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            buying_price decimal(10,2) NOT NULL,
            selling_price decimal(10,2) NOT NULL,
            image_url varchar(500) DEFAULT '',
            stock_available tinyint(1) DEFAULT 1,
            advance_payment_available tinyint(1) DEFAULT 1,
            stock_markup_percent decimal(5,2) DEFAULT 0,
            advance_discount_percent decimal(5,2) DEFAULT 0,
            external_id varchar(255) DEFAULT '',
            is_active tinyint(1) DEFAULT 1,
            is_demo tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY weight (weight),
            KEY status (status),
            KEY is_active (is_active),
            KEY is_demo (is_demo),
            KEY external_id (external_id),
            KEY article_number (article_number)
        ) $charset_collate;";

        // Submits table (formerly quotes)
        $sql_submits = "CREATE TABLE {$wpdb->prefix}gcc_submits (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            comment text,
            budget varchar(50) NOT NULL,
            type varchar(50) NOT NULL,
            delivery varchar(50) NOT NULL,
            persona varchar(50) NOT NULL,
            selected_products text,
            total_amount decimal(10,2) DEFAULT 0,
            ip_address varchar(45) NOT NULL,
            platform varchar(100) DEFAULT '',
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            customer_email varchar(255) NOT NULL,
            system_email varchar(255) NOT NULL,
            PRIMARY KEY (id),
            KEY email (email),
            KEY ip_address (ip_address),
            KEY created_date (created_date)
        ) $charset_collate;";

        // Chat personas table
        $sql_personas = "CREATE TABLE $this->table_personas (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            greeting_message text NOT NULL,
            image_url varchar(500) DEFAULT '',
            active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY active (active),
            KEY name (name)
        ) $charset_collate;";

        // Chatbot Questions table
        $sql_questions = "CREATE TABLE $this->table_questions (
            id int(11) NOT NULL AUTO_INCREMENT,
            question text NOT NULL,
            options text NOT NULL,
            attributes text DEFAULT '',
            question_order int(11) DEFAULT 0,
            active tinyint(1) DEFAULT 1,
            condition_logic text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY question_order (question_order),
            KEY active (active)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_products);
        dbDelta($sql_submits);
        dbDelta($sql_personas);
        dbDelta($sql_questions);

        // Insert default products and personas only if tables are empty
        $this->insert_sample_products();
        $this->insert_default_personas();
        $this->insert_default_questions();
    }


    private function insert_sample_products()
    {
        global $wpdb;

        $sample_products = array(
            array(
                'name' => 'Zlatna poluga 1g PAMP Suisse',
                'description' => 'Zlatna poluga renomiranog švajcarskog proizvođača PAMP Suisse. Čistoća 999.9 finoga zlata.',
                'external_id' => 'ZP001-1',
                'external_id' => 'ZP001-1',
                'type' => 'bar',
                'weight' => '1g',
                'price_net' => 80.00,
                'price_gross' => 95.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 80.00,
                'selling_price' => 95.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 2.5g PAMP Suisse',
                'description' => 'Zlatna poluga PAMP Suisse u veracash blisteru. Garantovana autentičnost i čistoća.',
                'external_id' => 'ZP001-2',
                'external_id' => 'ZP001-2',
                'type' => 'bar',
                'weight' => '2.5g',
                'price_net' => 200.00,
                'price_gross' => 220.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 200.00,
                'selling_price' => 220.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 5g PAMP Suisse',
                'description' => 'Zlatna poluga PAMP Suisse 999.9. Dolazi u originalnom blisteru sa certifikatom.',
                'external_id' => 'ZP001-3',
                'external_id' => 'ZP001-3',
                'type' => 'bar',
                'weight' => '5g',
                'price_net' => 400.00,
                'price_gross' => 435.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 400.00,
                'selling_price' => 435.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 10g PAMP Suisse',
                'description' => 'Zlatna poluga od 10 grama PAMP Suisse. Investicijska čistoća zlata 999.9.',
                'external_id' => 'ZP001-4',
                'external_id' => 'ZP001-4',
                'type' => 'bar',
                'weight' => '10g',
                'price_net' => 800.00,
                'price_gross' => 850.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 800.00,
                'selling_price' => 850.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 20g PAMP Suisse',
                'description' => 'Zlatna poluga 20 grama PAMP Suisse. Standardni format za investicije u zlato.',
                'external_id' => 'ZP001-5',
                'external_id' => 'ZP001-5',
                'type' => 'bar',
                'weight' => '20g',
                'price_net' => 1600.00,
                'price_gross' => 1700.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 1600.00,
                'selling_price' => 1700.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 50g PAMP Suisse',
                'description' => 'Zlatna poluga 50 grama PAMP Suisse. Velik investicijski format sa nižom premijom.',
                'external_id' => 'ZP001-6',
                'external_id' => 'ZP001-6',
                'type' => 'bar',
                'weight' => '50g',
                'price_net' => 4000.00,
                'price_gross' => 4200.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 4000.00,
                'selling_price' => 4200.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 100g PAMP Suisse',
                'description' => 'Zlatna poluga 100 grama PAMP Suisse. Veliki investicijski format.',
                'external_id' => 'ZP001-7',
                'external_id' => 'ZP001-7',
                'type' => 'bar',
                'weight' => '100g',
                'price_net' => 8000.00,
                'price_gross' => 8400.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 8000.00,
                'selling_price' => 8400.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 250g PAMP Suisse',
                'description' => 'Zlatna poluga 250 grama PAMP Suisse. Profesionalni investicijski format.',
                'external_id' => 'ZP001-8',
                'external_id' => 'ZP001-8',
                'type' => 'bar',
                'weight' => '250g',
                'price_net' => 20000.00,
                'price_gross' => 20500.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 20000.00,
                'selling_price' => 20500.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 500g PAMP Suisse',
                'description' => 'Zlatna poluga 500 grama PAMP Suisse. Veliki investicijski format sa najnižom premijom.',
                'external_id' => 'ZP001-9',
                'external_id' => 'ZP001-9',
                'type' => 'bar',
                'weight' => '500g',
                'price_net' => 40000.00,
                'price_gross' => 41000.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 40000.00,
                'selling_price' => 41000.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 1000g (1kg) PAMP Suisse',
                'description' => 'Zlatna poluga 1 kilogram PAMP Suisse. Standardni investicijski format za velike investicije.',
                'external_id' => 'ZP001-10',
                'external_id' => 'ZP001-10',
                'type' => 'bar',
                'weight' => '1000g',
                'price_net' => 80000.00,
                'price_gross' => 82000.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 80000.00,
                'selling_price' => 82000.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Dukat - Austrijski Franc Jozef I',
                'description' => 'Austrijski dukat Franca Jozefa I. Istorijska zlatna kovanica čistoće 986.',
                'external_id' => 'DU-001',
                'external_id' => 'DU-001',
                'type' => 'ducat',
                'weight' => '3.49g',
                'price_net' => 270.00,
                'price_gross' => 310.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 270.00,
                'selling_price' => 310.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Dukat - Austrijski Četiri Florensa',
                'description' => 'Austrijski dukat od četiri florensa. Težak 13.96g čistoća 986.',
                'external_id' => 'DU-002',
                'external_id' => 'DU-002',
                'type' => 'ducat',
                'weight' => '13.96g',
                'price_net' => 1100.00,
                'price_gross' => 1200.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 1100.00,
                'selling_price' => 1200.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Britannia 1oz',
                'description' => 'Britanska zlatna kovanica Britannia od jedne unce. Čistoća 999.9.',
                'external_id' => 'BR-001',
                'external_id' => 'BR-001',
                'type' => 'ducat',
                'weight' => '31.1g',
                'price_net' => 2400.00,
                'price_gross' => 2550.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 2400.00,
                'selling_price' => 2550.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Britannia 1/2oz',
                'description' => 'Britanska zlatna kovanica Britannia od pola unce. Čistoća 999.9.',
                'external_id' => 'BR-002',
                'external_id' => 'BR-002',
                'type' => 'ducat',
                'weight' => '15.55g',
                'price_net' => 1250.00,
                'price_gross' => 1350.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 1250.00,
                'selling_price' => 1350.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Britannia 1/4oz',
                'description' => 'Britanska zlatna kovanica Britannia od četvrt unce. Čistoća 999.9.',
                'external_id' => 'BR-003',
                'external_id' => 'BR-003',
                'type' => 'ducat',
                'weight' => '7.78g',
                'price_net' => 650.00,
                'price_gross' => 720.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 650.00,
                'selling_price' => 720.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Britannia 1/10oz',
                'description' => 'Britanska zlatna kovanica Britannia od desetine unce. Čistoća 999.9.',
                'external_id' => 'BR-004',
                'external_id' => 'BR-004',
                'type' => 'ducat',
                'weight' => '3.11g',
                'price_net' => 280.00,
                'price_gross' => 320.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 280.00,
                'selling_price' => 320.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Philharmoniker 1oz',
                'description' => 'Austrijska zlatna kovanica Philharmoniker od jedne unce. Čistoća 999.9.',
                'external_id' => 'PH-001',
                'external_id' => 'PH-001',
                'type' => 'ducat',
                'weight' => '31.1g',
                'price_net' => 2450.00,
                'price_gross' => 2600.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 2450.00,
                'selling_price' => 2600.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Philharmoniker 1/2oz',
                'description' => 'Austrijska zlatna kovanica Philharmoniker od pola unce. Čistoća 999.9.',
                'external_id' => 'PH-002',
                'external_id' => 'PH-002',
                'type' => 'ducat',
                'weight' => '15.55g',
                'price_net' => 1280.00,
                'price_gross' => 1380.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 1280.00,
                'selling_price' => 1380.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Philharmoniker 1/4oz',
                'description' => 'Austrijska zlatna kovanica Philharmoniker od četvrt unce. Čistoća 999.9.',
                'external_id' => 'PH-003',
                'external_id' => 'PH-003',
                'type' => 'ducat',
                'weight' => '7.78g',
                'price_net' => 670.00,
                'price_gross' => 740.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 670.00,
                'selling_price' => 740.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Philharmoniker 1/10oz',
                'description' => 'Austrijska zlatna kovanica Philharmoniker od desetine unce. Čistoća 999.9.',
                'external_id' => 'PH-004',
                'external_id' => 'PH-004',
                'type' => 'ducat',
                'weight' => '3.11g',
                'price_net' => 290.00,
                'price_gross' => 330.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 290.00,
                'selling_price' => 330.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Krügerrand 1oz',
                'description' => 'Južnoafrička zlatna kovanica Krügerrand od jedne unce. Čistoća 916.7.',
                'external_id' => 'KR-001',
                'external_id' => 'KR-001',
                'type' => 'ducat',
                'weight' => '33.93g',
                'price_net' => 2350.00,
                'price_gross' => 2500.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 2350.00,
                'selling_price' => 2500.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Krügerrand 1/2oz',
                'description' => 'Južnoafrička zlatna kovanica Krügerrand od pola unce. Čistoća 916.7.',
                'external_id' => 'KR-002',
                'external_id' => 'KR-002',
                'type' => 'ducat',
                'weight' => '16.97g',
                'price_net' => 1200.00,
                'price_gross' => 1300.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 1200.00,
                'selling_price' => 1300.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Krügerrand 1/4oz',
                'description' => 'Južnoafrička zlatna kovanica Krügerrand od četvrt unce. Čistoća 916.7.',
                'external_id' => 'KR-003',
                'external_id' => 'KR-003',
                'type' => 'ducat',
                'weight' => '8.48g',
                'price_net' => 620.00,
                'price_gross' => 690.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 620.00,
                'selling_price' => 690.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Krügerrand 1/10oz',
                'description' => 'Južnoafrička zlatna kovanica Krügerrand od desetine unce. Čistoća 916.7.',
                'external_id' => 'KR-004',
                'external_id' => 'KR-004',
                'type' => 'ducat',
                'weight' => '3.39g',
                'price_net' => 270.00,
                'price_gross' => 310.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 270.00,
                'selling_price' => 310.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Maple Leaf 1oz',
                'description' => 'Kanadska zlatna kovanica Maple Leaf od jedne unce. Čistoća 999.9.',
                'external_id' => 'ML-001',
                'external_id' => 'ML-001',
                'type' => 'ducat',
                'weight' => '31.1g',
                'price_net' => 2480.00,
                'price_gross' => 2630.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 2480.00,
                'selling_price' => 2630.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Maple Leaf 1/2oz',
                'description' => 'Kanadska zlatna kovanica Maple Leaf od pola unce. Čistoća 999.9.',
                'external_id' => 'ML-002',
                'external_id' => 'ML-002',
                'type' => 'ducat',
                'weight' => '15.55g',
                'price_net' => 1300.00,
                'price_gross' => 1400.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 1300.00,
                'selling_price' => 1400.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Maple Leaf 1/4oz',
                'description' => 'Kanadska zlatna kovanica Maple Leaf od četvrt unce. Čistoća 999.9.',
                'external_id' => 'ML-003',
                'external_id' => 'ML-003',
                'type' => 'ducat',
                'weight' => '7.78g',
                'price_net' => 690.00,
                'price_gross' => 760.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 690.00,
                'selling_price' => 760.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik Maple Leaf 1/10oz',
                'description' => 'Kanadska zlatna kovanica Maple Leaf od desetine unce. Čistoća 999.9.',
                'external_id' => 'ML-004',
                'external_id' => 'ML-004',
                'type' => 'ducat',
                'weight' => '3.11g',
                'price_net' => 300.00,
                'price_gross' => 340.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 300.00,
                'selling_price' => 340.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik American Eagle 1oz',
                'description' => 'Američka zlatna kovanica American Eagle od jedne unce. Čistoća 916.7.',
                'external_id' => 'AE-001',
                'external_id' => 'AE-001',
                'type' => 'ducat',
                'weight' => '33.93g',
                'price_net' => 2520.00,
                'price_gross' => 2670.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 2520.00,
                'selling_price' => 2670.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik American Eagle 1/2oz',
                'description' => 'Američka zlatna kovanica American Eagle od pola unce. Čistoća 916.7.',
                'external_id' => 'AE-002',
                'external_id' => 'AE-002',
                'type' => 'ducat',
                'weight' => '16.97g',
                'price_net' => 1330.00,
                'price_gross' => 1430.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 1330.00,
                'selling_price' => 1430.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik American Eagle 1/4oz',
                'description' => 'Američka zlatna kovanica American Eagle od četvrt unce. Čistoća 916.7.',
                'external_id' => 'AE-003',
                'external_id' => 'AE-003',
                'type' => 'ducat',
                'weight' => '8.48g',
                'price_net' => 710.00,
                'price_gross' => 780.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 710.00,
                'selling_price' => 780.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatnik American Eagle 1/10oz',
                'description' => 'Američka zlatna kovanica American Eagle od desetine unce. Čistoća 916.7.',
                'external_id' => 'AE-004',
                'external_id' => 'AE-004',
                'type' => 'ducat',
                'weight' => '3.39g',
                'price_net' => 310.00,
                'price_gross' => 350.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 310.00,
                'selling_price' => 350.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 1oz Heraeus',
                'description' => 'Zlatna poluga od jedne unce nemačkog proizvođača Heraeus. Čistoća 999.9.',
                'external_id' => 'HE-001',
                'external_id' => 'HE-001',
                'type' => 'bar',
                'weight' => '31.1g',
                'price_net' => 2400.00,
                'price_gross' => 2520.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 2400.00,
                'selling_price' => 2520.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 25x1g PAMP Suisse Multicard',
                'description' => 'Multicard sa 25 zlatnih poluga od 1 grama. Svaka poluga je u posebnom perforiranom blisteru.',
                'external_id' => 'ZP001-MC25',
                'external_id' => 'ZP001-MC25',
                'type' => 'bar',
                'weight' => '25g',
                'price_net' => 2000.00,
                'price_gross' => 2200.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 2000.00,
                'selling_price' => 2200.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 10x1g PAMP Suisse Multicard',
                'description' => 'Multicard sa 10 zlatnih poluga od 1 grama. Idealno za darove ili manje investicije.',
                'external_id' => 'ZP001-MC10',
                'external_id' => 'ZP001-MC10',
                'type' => 'bar',
                'weight' => '10g',
                'price_net' => 850.00,
                'price_gross' => 920.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 850.00,
                'selling_price' => 920.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna poluga 5x1g PAMP Suisse Multicard',
                'description' => 'Multicard sa 5 zlatnih poluga od 1 grama. Perfekcija švajcarskog kvaliteta.',
                'external_id' => 'ZP001-MC5',
                'external_id' => 'ZP001-MC5',
                'type' => 'bar',
                'weight' => '5g',
                'price_net' => 450.00,
                'price_gross' => 500.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 450.00,
                'selling_price' => 500.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna pločica Argor Heraeus 10g',
                'description' => 'Zlatna pločica švajcarsko-nemačke proizvodnje. Investicijska čistoća 999.9.',
                'external_id' => 'AH-001',
                'external_id' => 'AH-001',
                'type' => 'bar',
                'weight' => '10g',
                'price_net' => 820.00,
                'price_gross' => 880.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 820.00,
                'selling_price' => 880.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna pločica Argor Heraeus 20g',
                'description' => 'Zlatna pločica 20 grama Argor Heraeus. Investicijski format sa certifikatom.',
                'external_id' => 'AH-002',
                'external_id' => 'AH-002',
                'type' => 'bar',
                'weight' => '20g',
                'price_net' => 1620.00,
                'price_gross' => 1720.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 1620.00,
                'selling_price' => 1720.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna pločica Valcambi 50g',
                'description' => 'Zlatna pločica švajcarskog proizvođača Valcambi. Format 50g sa hologramom.',
                'external_id' => 'VC-001',
                'external_id' => 'VC-001',
                'type' => 'bar',
                'weight' => '50g',
                'price_net' => 4050.00,
                'price_gross' => 4250.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 4050.00,
                'selling_price' => 4250.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            ),
            array(
                'name' => 'Zlatna pločica Valcambi 100g',
                'description' => 'Zlatna pločica 100 grama Valcambi. Profesionalni investicijski format.',
                'external_id' => 'VC-002',
                'external_id' => 'VC-002',
                'type' => 'bar',
                'weight' => '100g',
                'price_net' => 8100.00,
                'price_gross' => 8500.00,
                'number_products' => 1000,
                'status' => 'published',
                'featured_image' => '',
                'buying_price' => 8100.00,
                'selling_price' => 8500.00,
                'image_url' => '',
                'stock_available' => 1,
                'advance_payment_available' => 1,
                'stock_markup_percent' => 4.0,
                'advance_discount_percent' => 3.0
            )
        );

        foreach ($sample_products as $product) {
            $wpdb->insert($this->table_products, $product);
        }
    }

    public function get_products_for_budget($budget, $type = 'all', $delivery_method = 'stock')
    {
        global $wpdb;

        // Handle combo type - get both bars and ducats
        if ($type === 'combo') {
            $where_type = "AND type IN ('bar', 'ducat')";
            $query = "SELECT * FROM $this->table_products WHERE status = 'published' AND is_active = 1 $where_type ORDER BY price_gross ASC";
            $products = $wpdb->get_results($query);
        } else if ($type !== 'all') {
            $query = "SELECT * FROM $this->table_products WHERE status = 'published' AND is_active = 1 AND type = %s ORDER BY price_gross ASC";
            $products = $wpdb->get_results($wpdb->prepare($query, $type));
        } else {
            $query = "SELECT * FROM $this->table_products WHERE status = 'published' AND is_active = 1 ORDER BY price_gross ASC";
            $products = $wpdb->get_results($query);
        }

        // Filter products that fit in budget and apply delivery method pricing
        $filtered_products = array();
        if ($products) {
            foreach ($products as $product) {
                $final_price = $product->price_gross;

                if ($delivery_method === 'stock') {
                    $final_price = $product->selling_price * (1 + $product->stock_markup_percent / 100);
                } else {
                    $final_price = $product->selling_price * (1 - $product->advance_discount_percent / 100);
                }

                if ($final_price <= $budget) {
                    $product->final_price = $final_price;
                    $filtered_products[] = $product;
                }
            }
        }

        return $filtered_products;
    }

    public function save_submit($data)
    {
        global $wpdb;

        $submit_data = array(
            'name' => sanitize_text_field($data['name']),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone']),
            'comment' => sanitize_textarea_field($data['message'] ?? $data['comment'] ?? ''),
            'budget' => sanitize_text_field($data['budget_display'] ?? $data['budget'] ?? ''),
            'type' => sanitize_text_field($data['product_type'] ?? $data['type'] ?? ''),
            'delivery' => sanitize_text_field($data['delivery_method'] ?? $data['delivery'] ?? ''),
            'persona' => sanitize_text_field($data['persona'] ?? 'ZLATIJA'),
            'selected_products' => isset($data['selected_products']) ? wp_json_encode($data['selected_products']) : '',
            'total_amount' => isset($data['total_value']) ? floatval($data['total_value']) : (isset($data['total_amount']) ? floatval($data['total_amount']) : 0),
            'ip_address' => $this->get_client_ip(),
            'platform' => $this->get_user_platform(),
            'created_date' => current_time('mysql'),
            'customer_email' => sanitize_email($data['email']),
            'system_email' => get_option('gcc_notification_email', get_option('admin_email'))
        );

        $result = $wpdb->insert($this->table_submits, $submit_data);

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    private function get_client_ip()
    {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field($ip);
    }

    private function get_user_platform()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $platform = 'Unknown';

        if (preg_match('/windows|win32/i', $user_agent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
            $platform = 'Mac';
        } elseif (preg_match('/linux/i', $user_agent)) {
            $platform = 'Linux';
        } elseif (preg_match('/ubuntu/i', $user_agent)) {
            $platform = 'Ubuntu';
        } elseif (preg_match('/iphone/i', $user_agent)) {
            $platform = 'iPhone';
        } elseif (preg_match('/android/i', $user_agent)) {
            $platform = 'Android';
        }

        return sanitize_text_field($platform);
    }

    public function get_products_ajax()
    {
        try {
            // Check nonce only if it's provided
            if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
                check_ajax_referer('gcc_nonce', 'nonce');
            }

            $budget = floatval($_POST['budget']);
            $type = sanitize_text_field($_POST['type']);
            $delivery_method = sanitize_text_field($_POST['delivery_method']);

            // Validate input
            if ($budget <= 0) {
                wp_send_json_error(array('message' => 'Invalid budget amount'));
                return;
            }

            // Check if table exists
            global $wpdb;
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$this->table_products'");
            if (!$table_exists) {
                error_log('GCC products table does not exist');
                wp_send_json_error(array('message' => 'Database table not found'));
                return;
            }

            // Check if we have any products
            $product_count = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_products");
            if ($product_count == 0) {
                error_log('GCC no products found in database');
                // Try to create tables and add demo products
                $this->create_tables();
                $this->add_demo_products_on_activation();
            }

            $products = $this->get_products_for_budget($budget, $type, $delivery_method);

            wp_send_json_success($products);
        } catch (Exception $e) {
            error_log('GCC get_products_ajax error: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Server error occurred: ' . $e->getMessage()));
        }
    }

    public function test_database_ajax()
    {
        try {
            global $wpdb;

            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$this->table_products'");
            $table_info = array('table_exists' => $table_exists ? true : false);

            if ($table_exists) {
                $product_count = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_products");
                $table_info['product_count'] = $product_count;

                // Get sample products
                $sample_products = $wpdb->get_results("SELECT id, name, type, price_gross, status, is_active FROM $this->table_products LIMIT 5");
                $table_info['sample_products'] = $sample_products;
            } else {
                // Try to create tables
                $this->create_tables();
                $this->add_demo_products_on_activation();
                $table_info['attempted_creation'] = true;
            }

            wp_send_json_success($table_info);
        } catch (Exception $e) {
            wp_send_json_error(array('message' => 'Test error: ' . $e->getMessage()));
        }
    }

    public function get_chatbot_questions_ajax()
    {
        try {
            // Check nonce only if it's provided
            if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
                check_ajax_referer('gcc_nonce', 'nonce');
            }

            $user_answers = isset($_POST['user_answers']) ? json_decode(stripslashes($_POST['user_answers']), true) : array();

            // Check if table exists
            global $wpdb;
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$this->table_questions'");
            if (!$table_exists) {
                error_log('GCC questions table does not exist');
                // Try to create tables
                $this->create_tables();
            }

            // Check if we have any questions
            $question_count = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_questions");
            if ($question_count == 0) {
                error_log('GCC no questions found in database');
                // Try to insert default questions
                $this->insert_default_questions();
            }

            // Get questions filtered by conditions
            $questions = $this->get_questions_for_chatbot($user_answers);


            wp_send_json_success($questions);
        } catch (Exception $e) {
            error_log('GCC get_chatbot_questions_ajax error: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Server error occurred: ' . $e->getMessage()));
        }
    }

    public function get_all_chatbot_questions_ajax()
    {
        try {
            // Check nonce only if it's provided
            if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
                #check_ajax_referer('gcc_nonce', 'nonce');
            }

            // Check if table exists
            global $wpdb;
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$this->table_questions'");
            if (!$table_exists) {
                error_log('GCC questions table does not exist');
                // Try to create tables
                $this->create_tables();
            }

            // Check if we have any questions
            $question_count = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_questions");
            if ($question_count == 0) {
                error_log('GCC no questions found in database');
                // Try to insert default questions
                $this->insert_default_questions();
            }

            // Get all active questions without filtering
            $questions = $this->get_active_questions();

            wp_send_json_success($questions);
        } catch (Exception $e) {
            error_log('GCC get_all_chatbot_questions_ajax error: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Server error occurred: ' . $e->getMessage()));
        }
    }

    public function calculate_optimal_products_ajax()
    {
        try {
            // Check nonce only if it's provided
            if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
                #check_ajax_referer('gcc_nonce', 'nonce');
            }

            $budget = floatval($_POST['budget']);
            $product_type = sanitize_text_field($_POST['product_type']);
            $combo_percentage = isset($_POST['combo_percentage']) ? intval($_POST['combo_percentage']) : 60;
            $weight_preference = sanitize_text_field($_POST['weight_preference']);
            $delivery_method = sanitize_text_field($_POST['delivery_method']);

            // Validate input
            if ($budget <= 0) {
                wp_send_json_error(array('message' => 'Invalid budget amount'));
                return;
            }

            // Calculate optimal products
            $result = $this->calculate_optimal_product_combination($budget, $product_type, $combo_percentage, $weight_preference, $delivery_method);

            wp_send_json_success($result);
        } catch (Exception $e) {
            error_log('GCC calculate_optimal_products_ajax error: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Server error occurred: ' . $e->getMessage()));
        }
    }

    public function submit_contact_ajax()
    {
        //         $fp = fopen(__DIR__ . '/xxxx.txt', 'a');
        //         fwrite($fp, print_r('submit_contact_ajax', true) . '
        // ================================
        // ');
        //         fclose($fp);
        try {
            // Check nonce only if it's provided
            if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
                #check_ajax_referer('gcc_nonce', 'nonce');
            }

            // Validate required fields
            if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['phone'])) {
                wp_send_json_error(array('message' => 'Missing required fields'));
                return;
            }

            // Prepare contact data
            $contact_data = array(
                'name' => sanitize_text_field($_POST['name']),
                'email' => sanitize_email($_POST['email']),
                'phone' => sanitize_text_field($_POST['phone']),
                'message' => sanitize_textarea_field($_POST['message']),
                'budget' => intval($_POST['budget']),
                'budget_display' => sanitize_text_field($_POST['budget_display']),
                'product_type' => sanitize_text_field($_POST['product_type']),
                'combo_percentage' => intval($_POST['combo_percentage']),
                'weight_preference' => sanitize_text_field($_POST['weight_preference']),
                'delivery_method' => sanitize_text_field($_POST['delivery_method']),
                'selected_products' => isset($_POST['selected_products']) ? $_POST['selected_products'] : array(),
                'total_value' => floatval($_POST['total_value']),
                'quote_type' => sanitize_text_field($_POST['quote_type'])
            );

            // Save to database
            $result = $this->save_submit($contact_data);

            if ($result) {
                // Send email notification
                $this->send_email_notification($contact_data, $result);

                wp_send_json_success(array('message' => 'Contact submitted successfully', 'id' => $result));
            } else {
                wp_send_json_error(array('message' => 'Failed to save contact'));
            }
        } catch (Exception $e) {
            error_log('GCC submit_contact_ajax error: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Server error occurred: ' . $e->getMessage()));
        }
    }

    private function send_email_notification($contact_data, $ticket_id)
    {
        // Load email handler
        if (!class_exists('GCC_Email_Handler')) {
            require_once GCC_PLUGIN_PATH . 'includes/class-email-handler.php';
        }

        $email_handler = new GCC_Email_Handler();
        $ticket_number = 'GCC-' . date('Y') . '-' . str_pad($ticket_id, 5, '0', STR_PAD_LEFT);

        // Prepare email data
        $email_data = array(
            'name' => $contact_data['name'],
            'email' => $contact_data['email'],
            'phone' => $contact_data['phone'],
            'message' => $contact_data['message'],
            'budget_range' => $contact_data['budget_display'],
            'product_type' => $contact_data['product_type'],
            'delivery_method' => $contact_data['delivery_method'],
            'weight_preference' => $contact_data['weight_preference'],
            'selected_products' => $contact_data['selected_products'],
            'total_value' => $contact_data['total_value'],
            'quote_type' => $contact_data['quote_type']
        );

        return $email_handler->send_quote_emails($email_data, $ticket_number);
    }

    public function create_product($data)
    {
        global $wpdb;

        $product_data = array(
            'name' => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description']),
            'article_number' => sanitize_text_field($data['article_number']),
            'type' => sanitize_text_field($data['type']),
            'weight' => sanitize_text_field($data['weight']),
            'price_net' => floatval($data['price_net']),
            'price_gross' => floatval($data['price_gross']),
            'number_products' => intval($data['number_products']),
            'status' => sanitize_text_field($data['status']),
            'featured_image' => esc_url_raw($data['featured_image']),
            'buying_price' => floatval($data['buying_price']),
            'selling_price' => floatval($data['selling_price']),
            'image_url' => esc_url_raw($data['image_url']),
            'stock_available' => isset($data['stock_available']) ? 1 : 0,
            'advance_payment_available' => isset($data['advance_payment_available']) ? 1 : 0,
            'stock_markup_percent' => floatval($data['stock_markup_percent']),
            'advance_discount_percent' => floatval($data['advance_discount_percent']),
            'external_id' => sanitize_text_field($data['external_id']),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'price_updated_at' => current_time('mysql')
        );

        $result = $wpdb->insert($this->table_products, $product_data);

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    public function update_product($id, $data)
    {
        global $wpdb;

        $product_data = array(
            'name' => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description']),
            'article_number' => sanitize_text_field($data['article_number']),
            'type' => sanitize_text_field($data['type']),
            'weight' => sanitize_text_field($data['weight']),
            'price_net' => floatval($data['price_net']),
            'price_gross' => floatval($data['price_gross']),
            'number_products' => intval($data['number_products']),
            'status' => sanitize_text_field($data['status']),
            'featured_image' => esc_url_raw($data['featured_image']),
            'buying_price' => floatval($data['buying_price']),
            'selling_price' => floatval($data['selling_price']),
            'image_url' => esc_url_raw($data['image_url']),
            'stock_available' => isset($data['stock_available']) ? 1 : 0,
            'advance_payment_available' => isset($data['advance_payment_available']) ? 1 : 0,
            'stock_markup_percent' => floatval($data['stock_markup_percent']),
            'advance_discount_percent' => floatval($data['advance_discount_percent']),
            'external_id' => sanitize_text_field($data['external_id']),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'price_updated_at' => current_time('mysql')
        );

        $result = $wpdb->update($this->table_products, $product_data, array('id' => $id));

        return $result !== false;
    }

    public function delete_product($id)
    {
        global $wpdb;

        $result = $wpdb->delete($this->table_products, array('id' => $id));

        return $result !== false;
    }

    public function get_product($id)
    {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->table_products WHERE id = %d", $id));
    }

    public function get_all_products()
    {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM $this->table_products ORDER BY created_at DESC");
    }

    public function get_products_paginated($page = 1, $per_page = 10, $search = '', $order_by = 'created_at', $order = 'DESC')
    {
        global $wpdb;

        $offset = ($page - 1) * $per_page;

        $where = '';
        if (!empty($search)) {
            $where = $wpdb->prepare(
                "WHERE name LIKE %s OR article_number LIKE %s OR type LIKE %s",
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }

        $valid_order_by = ['name', 'article_number', 'type', 'weight', 'price_net', 'price_gross', 'status', 'created_at'];
        if (!in_array($order_by, $valid_order_by)) {
            $order_by = 'created_at';
        }

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $query = "SELECT * FROM $this->table_products $where ORDER BY $order_by $order LIMIT $per_page OFFSET $offset";

        return $wpdb->get_results($query);
    }

    public function get_products_count($search = '')
    {
        global $wpdb;

        $where = '';
        if (!empty($search)) {
            $where = $wpdb->prepare(
                "WHERE name LIKE %s OR article_number LIKE %s OR type LIKE %s",
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }

        return $wpdb->get_var("SELECT COUNT(*) FROM $this->table_products $where");
    }

    public function delete_submit($id)
    {
        global $wpdb;

        $result = $wpdb->delete($this->table_submits, array('id' => $id));

        return $result !== false;
    }

    public function get_all_submits()
    {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM $this->table_submits ORDER BY created_date DESC");
    }

    public function get_submits_paginated($page = 1, $per_page = 10, $search = '', $order_by = 'created_date', $order = 'DESC')
    {
        global $wpdb;

        $offset = ($page - 1) * $per_page;

        $where = '';
        if (!empty($search)) {
            $where = $wpdb->prepare(
                "WHERE name LIKE %s OR email LIKE %s OR phone LIKE %s",
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }

        $valid_order_by = ['name', 'email', 'phone', 'created_date', 'budget', 'type', 'delivery'];
        if (!in_array($order_by, $valid_order_by)) {
            $order_by = 'created_date';
        }

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $query = "SELECT * FROM $this->table_submits $where ORDER BY $order_by $order LIMIT $per_page OFFSET $offset";

        return $wpdb->get_results($query);
    }

    public function get_submits_count($search = '')
    {
        global $wpdb;

        $where = '';
        if (!empty($search)) {
            $where = $wpdb->prepare(
                "WHERE name LIKE %s OR email LIKE %s OR phone LIKE %s",
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }

        return $wpdb->get_var("SELECT COUNT(*) FROM $this->table_submits $where");
    }

    public function add_demo_products_on_activation()
    {
        global $wpdb;

        // Check if any products already exist (not just demo)
        $product_count = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_products");

        if ($product_count > 0) {
            return; // Products already exist, don't overwrite
        }

        // Use CSV data as default products on activation
        $this->insert_sample_products();
    }

    // === PERSONA METHODS ===

    private function insert_default_personas()
    {
        global $wpdb;

        // Check if personas already exist
        $persona_count = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_personas");

        if ($persona_count > 0) {
            return; // Personas already exist
        }

        // Default personas to add
        $default_personas = array(
            array(
                'name' => 'ZLATIJA',
                'greeting_message' => 'Zdravo! Ja sam ZLATIJA – vaš vodič kroz svet investicionog zlata. Hajde da pronađemo najbolji paket zlata za vaš budžet! 💰',
                'image_url' => '',
                'active' => 1
            ),
            array(
                'name' => 'ZLATA',
                'greeting_message' => 'Pozdrav! Ja sam ZLATA, vaš ekspert za investicije u zlato. Spremna sam da vam pomognem da pronađete savršenu investiciju! ✨',
                'image_url' => '',
                'active' => 1
            ),
            array(
                'name' => 'ZLATKA',
                'greeting_message' => 'Zdravo! Ja sam ZLATKA i tu sam da vam pomognem u svetu investicionog zlata. Hajde da napravimo pametnu investiciju zajedno! 🏆',
                'image_url' => '',
                'active' => 1
            ),
            array(
                'name' => 'ZLATISLAVA',
                'greeting_message' => 'Dobrodošli! Ja sam ZLATISLAVA, vaš savetnik za investicije u zlato. Spremna sam da vam ukažem na najbolje mogućnosti! 👑',
                'image_url' => '',
                'active' => 1
            )
        );

        $added_count = 0;
        foreach ($default_personas as $persona) {
            $result = $wpdb->insert($this->table_personas, $persona);
            if ($result) {
                $added_count++;
            }
        }

        if ($added_count > 0) {
            error_log("GCC Database: Added {$added_count} default personas on activation");
        }
    }

    public function get_all_personas()
    {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM $this->table_personas ORDER BY name ASC");
    }

    public function get_active_personas()
    {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM $this->table_personas WHERE active = 1 ORDER BY name ASC");
    }

    public function get_persona($id)
    {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->table_personas WHERE id = %d", $id));
    }

    public function get_random_active_persona()
    {
        global $wpdb;

        return $wpdb->get_row("SELECT * FROM $this->table_personas WHERE active = 1 ORDER BY RAND() LIMIT 1");
    }

    public function create_persona($data)
    {
        global $wpdb;

        $persona_data = array(
            'name' => sanitize_text_field($data['name']),
            'greeting_message' => sanitize_textarea_field($data['greeting_message']),
            'image_url' => esc_url_raw($data['image_url']),
            'active' => isset($data['active']) ? 1 : 0
        );

        $result = $wpdb->insert($this->table_personas, $persona_data);

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    public function update_persona($id, $data)
    {
        global $wpdb;

        $persona_data = array(
            'name' => sanitize_text_field($data['name']),
            'greeting_message' => sanitize_textarea_field($data['greeting_message']),
            'image_url' => esc_url_raw($data['image_url']),
            'active' => isset($data['active']) ? 1 : 0
        );

        $result = $wpdb->update($this->table_personas, $persona_data, array('id' => $id));

        return $result !== false;
    }

    public function delete_persona($id)
    {
        global $wpdb;

        $result = $wpdb->delete($this->table_personas, array('id' => $id));

        return $result !== false;
    }

    public function toggle_persona_active($id)
    {
        global $wpdb;

        $current_status = $wpdb->get_var($wpdb->prepare("SELECT active FROM $this->table_personas WHERE id = %d", $id));
        $new_status = $current_status ? 0 : 1;

        $result = $wpdb->update($this->table_personas, array('active' => $new_status), array('id' => $id));

        return $result !== false;
    }

    public function get_personas_paginated($page = 1, $per_page = 10, $search = '', $order_by = 'name', $order = 'ASC')
    {
        global $wpdb;

        $offset = ($page - 1) * $per_page;

        $where = '';
        if (!empty($search)) {
            $where = $wpdb->prepare(
                "WHERE name LIKE %s OR greeting_message LIKE %s",
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }

        $valid_order_by = ['name', 'active', 'created_at', 'updated_at'];
        if (!in_array($order_by, $valid_order_by)) {
            $order_by = 'name';
        }

        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $query = "SELECT * FROM $this->table_personas $where ORDER BY $order_by $order LIMIT $per_page OFFSET $offset";

        return $wpdb->get_results($query);
    }

    public function get_personas_count($search = '')
    {
        global $wpdb;

        $where = '';
        if (!empty($search)) {
            $where = $wpdb->prepare(
                "WHERE name LIKE %s OR greeting_message LIKE %s",
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }

        return $wpdb->get_var("SELECT COUNT(*) FROM $this->table_personas $where");
    }

    // === CHATBOT QUESTIONS METHODS ===

    private function insert_default_questions()
    {
        global $wpdb;

        // Check if questions already exist
        $question_count = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_questions");

        if ($question_count > 0) {
            return; // Questions already exist
        }

        // Default questions based on current chatbot flow
        $default_questions = array(
            array(
                'question' => 'Koliki je vaš budžet za investiciju u zlato?',
                'options' => json_encode(array(
                    array('value' => '1000', 'label' => '€1,000', 'display' => '€1,000', 'rsd' => '117,500 RSD'),
                    array('value' => '2500', 'label' => '€2,500', 'display' => '€2,500', 'rsd' => '293,750 RSD'),
                    array('value' => '5000', 'label' => '€5,000', 'display' => '€5,000', 'rsd' => '587,500 RSD'),
                    array('value' => '10000', 'label' => '€10,000', 'display' => '€10,000', 'rsd' => '1,175,000 RSD'),
                    array('value' => '20000', 'label' => '€20,000', 'display' => '€20,000', 'rsd' => '2,350,000 RSD'),
                    array('value' => '50000', 'label' => '€50,000+', 'display' => '€50,000+', 'rsd' => '5,875,000+ RSD')
                )),
                'attributes' => json_encode(array('budget' => true)),
                'question_order' => 1,
                'active' => 1,
                'condition_logic' => ''
            ),
            array(
                'question' => 'Odlično! Koji tip zlata preferirate?',
                'options' => json_encode(array(
                    array('value' => 'bars', 'label' => 'Samo poluge/pločice'),
                    array('value' => 'ducats', 'label' => 'Samo dukati'),
                    array('value' => 'combo', 'label' => 'Kombinacija poluga i dukata')
                )),
                'attributes' => json_encode(array('product_type' => true)),
                'question_order' => 2,
                'active' => 1,
                'condition_logic' => 'budget < 30000'
            ),
            array(
                'question' => 'Kakav procenat zlata želite?',
                'options' => json_encode(array(
                    array('value' => '50', 'label' => 'Pola Pola (50% poluge, 50% dukati)'),
                    array('value' => '33', 'label' => 'Više Dukata (33% poluge, 67% dukati)'),
                    array('value' => '67', 'label' => 'Više Poluga (67% poluge, 33% dukati)')
                )),
                'attributes' => json_encode(array('combo_percentage' => true)),
                'question_order' => 3,
                'active' => 1,
                'condition_logic' => 'product_type == "combo"'
            ),
            array(
                'question' => 'Da li preferirate:',
                'options' => json_encode(array(
                    array('value' => 'lighter', 'label' => 'Više lakših poluga', 'description' => '(likvidniji)'),
                    array('value' => 'heavier', 'label' => 'Manje težih poluga', 'description' => '(niža premija)')
                )),
                'attributes' => json_encode(array('weight_preference' => true)),
                'question_order' => 4,
                'active' => 1,
                'condition_logic' => 'product_type == "bars" || product_type == "combo"'
            ),
            array(
                'question' => 'Želite li:',
                'options' => json_encode(array(
                    array('value' => 'stock', 'label' => 'Sa stanja', 'description' => '(dostupno odmah, viša cena)'),
                    array('value' => 'advance', 'label' => 'Avansna isplata', 'description' => '(100% unapred, ~10 dana, niža cena)')
                )),
                'attributes' => json_encode(array('delivery_method' => true)),
                'question_order' => 5,
                'active' => 1,
                'condition_logic' => ''
            ),
            array(
                'question' => 'Za veće investicije preporučujemo direktan razgovor sa treiderom. Šta želite da uradite?',
                'options' => json_encode(array(
                    array('value' => 'schedule', 'label' => 'Zakaži razgovor sa treiderom'),
                    array('value' => 'continue', 'label' => 'Nastavi sa online kalkulacijom')
                )),
                'attributes' => json_encode(array('high_budget_action' => true)),
                'question_order' => 6,
                'active' => 1,
                'condition_logic' => 'budget >= 30000'
            )
        );

        $added_count = 0;
        foreach ($default_questions as $question) {
            $result = $wpdb->insert($this->table_questions, $question);
            if ($result) {
                $added_count++;
            }
        }

        if ($added_count > 0) {
            error_log("GCC Database: Added {$added_count} default questions on activation");
        }
    }

    public function refresh_default_questions()
    {
        global $wpdb;

        // Delete existing questions
        $wpdb->query("DELETE FROM $this->table_questions");

        // Insert fresh default questions
        $this->insert_default_questions();

        return true;
    }

    public function get_all_questions()
    {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM $this->table_questions ORDER BY question_order ASC");
    }

    public function get_active_questions()
    {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM $this->table_questions WHERE active = 1 ORDER BY question_order ASC");
    }

    public function get_questions_for_chatbot($user_answers = array())
    {
        global $wpdb;

        $questions = $wpdb->get_results("SELECT * FROM $this->table_questions WHERE active = 1 ORDER BY question_order ASC");

        // Filter questions based on conditions
        $filtered_questions = array();
        foreach ($questions as $question) {
            if ($this->evaluate_condition($question->condition_logic, $user_answers)) {
                $filtered_questions[] = $question;
            }
        }

        return $filtered_questions;
    }

    private function evaluate_condition($condition_logic, $user_answers)
    {
        // If no condition, always show
        if (empty($condition_logic)) {
            return true;
        }

        // Simple condition evaluator
        // Replace variable names with actual values
        $condition = $condition_logic;

        // Handle escaped quotes that might be saved in the database
        $condition = str_replace('\\"', '"', $condition);
        $condition = str_replace("\\'", "'", $condition);

        // Find all variables in the condition
        preg_match_all('/\b[a-zA-Z_][a-zA-Z0-9_]*\b/', $condition_logic, $matches);
        $variables = array_unique($matches[0]);

        // Filter out PHP keywords
        $php_keywords = array('true', 'false', 'null', 'and', 'or', 'xor', 'not');
        $variables = array_diff($variables, $php_keywords);

        // Check if all required variables are present
        foreach ($variables as $var) {
            if (!isset($user_answers[$var])) {
                error_log("GCC Condition '$condition_logic' requires variable '$var' which is not set");
                return false;
            }
        }

        // Replace variables with their values
        foreach ($user_answers as $key => $value) {
            $condition = str_replace($key, is_numeric($value) ? $value : '"' . $value . '"', $condition);
        }

        // Replace comparison operators
        $condition = str_replace('==', '===', $condition);
        $condition = str_replace('!=', '!==', $condition);


        // Simple PHP eval (be careful with user input)
        try {
            $result = @eval("return $condition;");
            return $result === true;
        } catch (Exception $e) {
            error_log("Question condition evaluation error: " . $e->getMessage() . " for condition: " . $condition);
            return true; // Default to showing the question if evaluation fails
        } catch (ParseError $e) {
            error_log("Question condition parse error: " . $e->getMessage() . " for condition: " . $condition);
            return true; // Default to showing the question if evaluation fails
        }
    }

    public function get_question($id)
    {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->table_questions WHERE id = %d", $id));
    }

    public function create_question($data)
    {
        global $wpdb;

        $question_data = array(
            'question' => sanitize_textarea_field($data['question']),
            'options' => wp_json_encode($data['options']),
            'attributes' => isset($data['attributes']) ? wp_json_encode($data['attributes']) : '',
            'question_order' => intval($data['question_order']),
            'active' => isset($data['active']) ? 1 : 0,
            'condition_logic' => isset($data['condition_logic']) ? $data['condition_logic'] : ''
        );

        $result = $wpdb->insert($this->table_questions, $question_data);

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    public function update_question($id, $data)
    {
        global $wpdb;

        $question_data = array(
            'question' => sanitize_textarea_field($data['question']),
            'options' => wp_json_encode($data['options']),
            'attributes' => isset($data['attributes']) ? wp_json_encode($data['attributes']) : '',
            'question_order' => intval($data['question_order']),
            'active' => isset($data['active']) ? 1 : 0,
            'condition_logic' => isset($data['condition_logic']) ? $data['condition_logic'] : ''
        );

        $result = $wpdb->update($this->table_questions, $question_data, array('id' => $id));

        return $result !== false;
    }

    public function delete_question($id)
    {
        global $wpdb;

        $result = $wpdb->delete($this->table_questions, array('id' => $id));

        return $result !== false;
    }

    public function update_question_order($id, $new_order)
    {
        global $wpdb;

        $result = $wpdb->update($this->table_questions, array('question_order' => $new_order), array('id' => $id));

        return $result !== false;
    }

    public function toggle_question_active($id)
    {
        global $wpdb;

        $current_status = $wpdb->get_var($wpdb->prepare("SELECT active FROM $this->table_questions WHERE id = %d", $id));
        $new_status = $current_status ? 0 : 1;

        $result = $wpdb->update($this->table_questions, array('active' => $new_status), array('id' => $id));

        return $result !== false;
    }

    public function get_budget_calculation($budget, $product_type = 'all', $delivery_method = 'stock')
    {
        global $wpdb;

        // Get suitable products for the budget
        $products = $this->get_products_for_budget($budget, $product_type, $delivery_method);

        if (empty($products)) {
            return array();
        }

        // Calculate optimal product combination
        $optimal_combination = $this->calculate_optimal_combination($products, $budget);

        return $optimal_combination;
    }

    public function calculate_optimal_product_combination($budget, $product_type, $combo_percentage = 60, $weight_preference = '', $delivery_method = 'stock')
    {
        global $wpdb;

        // Get all available products
        $all_products = $this->get_all_available_products($product_type, $delivery_method);

        if (empty($all_products)) {
            return array(
                'products' => array(),
                'total_value' => 0,
                'budget_used' => 0,
                'budget_remaining' => $budget
            );
        }

        // Apply final pricing based on delivery method
        foreach ($all_products as $product) {
            if ($delivery_method === 'stock') {
                $product->final_price = $product->selling_price * (1 + $product->stock_markup_percent / 100);
            } else {
                $product->final_price = $product->selling_price * (1 - $product->advance_discount_percent / 100);
            }
        }

        // Calculate optimal combination based on product type
        if ($product_type === 'combo') {
            return $this->calculate_combo_combination($all_products, $budget, $combo_percentage);
        } else {
            return $this->calculate_single_type_combination($all_products, $budget, $product_type, $weight_preference);
        }
    }

    private function get_all_available_products($product_type, $delivery_method)
    {
        global $wpdb;

        $where_clauses = array(
            "status = 'published'",
            "is_active = 1"
        );

        // Filter by product type
        if ($product_type === 'bars') {
            $where_clauses[] = "type = 'bar'";
        } elseif ($product_type === 'ducats') {
            $where_clauses[] = "type = 'ducat'";
        } elseif ($product_type === 'combo') {
            $where_clauses[] = "type IN ('bar', 'ducat')";
        }

        // Filter by delivery method availability
        if ($delivery_method === 'stock') {
            $where_clauses[] = "stock_available = 1";
        } else {
            $where_clauses[] = "advance_payment_available = 1";
        }

        $where_sql = implode(' AND ', $where_clauses);
        $query = "SELECT * FROM $this->table_products WHERE $where_sql ORDER BY type, price_gross ASC";

        return $wpdb->get_results($query);
    }

    private function calculate_combo_combination($products, $budget, $combo_percentage)
    {
        // Split budget according to percentage
        $bars_budget = $budget * ($combo_percentage / 100);
        $ducats_budget = $budget * ((100 - $combo_percentage) / 100);

        // Separate products by type
        $bars = array_filter($products, function ($p) {
            return $p->type === 'bar';
        });
        $ducats = array_filter($products, function ($p) {
            return $p->type === 'ducat';
        });

        // Calculate optimal combination for each type
        $bars_result = $this->calculate_single_type_combination($bars, $bars_budget, 'bars', '');
        $ducats_result = $this->calculate_single_type_combination($ducats, $ducats_budget, 'ducats', '');

        // Combine results
        $combined_products = array_merge($bars_result['products'], $ducats_result['products']);
        $total_value = $bars_result['total_value'] + $ducats_result['total_value'];

        // If we have remaining budget, try to optimize further
        $remaining_budget = $budget - $total_value;
        if ($remaining_budget > 0) {
            $optimized_result = $this->optimize_remaining_budget($combined_products, $products, $remaining_budget);
            $combined_products = $optimized_result['products'];
            $total_value = $optimized_result['total_value'];
        }

        return array(
            'products' => $combined_products,
            'total_value' => $total_value,
            'budget_used' => $total_value,
            'budget_remaining' => $budget - $total_value
        );
    }

    private function calculate_single_type_combination($products, $budget, $product_type, $weight_preference)
    {
        if (empty($products)) {
            return array(
                'products' => array(),
                'total_value' => 0,
                'budget_used' => 0,
                'budget_remaining' => $budget
            );
        }

        // Apply weight preference sorting
        if ($weight_preference === 'lighter') {
            // Sort by weight ascending (lighter first)
            usort($products, function ($a, $b) {
                $weight_a = $this->extract_weight_value($a->weight);
                $weight_b = $this->extract_weight_value($b->weight);
                return $weight_a <=> $weight_b;
            });
        } elseif ($weight_preference === 'heavier') {
            // Sort by weight descending (heavier first)
            usort($products, function ($a, $b) {
                $weight_a = $this->extract_weight_value($a->weight);
                $weight_b = $this->extract_weight_value($b->weight);
                return $weight_b <=> $weight_a;
            });
        } else {
            // Default: sort by price efficiency (price per gram)
            usort($products, function ($a, $b) {
                $weight_a = $this->extract_weight_value($a->weight);
                $weight_b = $this->extract_weight_value($b->weight);

                $efficiency_a = $a->final_price / $weight_a;
                $efficiency_b = $b->final_price / $weight_b;

                return $efficiency_a <=> $efficiency_b;
            });
        }

        // Use greedy algorithm to fill budget optimally
        $selected_products = array();
        $total_value = 0;
        $target_budget = $budget * 0.98; // Target 98% of budget

        foreach ($products as $product) {
            $remaining_budget = $budget - $total_value;

            // Skip if product is too expensive
            if ($product->final_price > $remaining_budget) {
                continue;
            }

            // Calculate how many of this product we can afford
            $max_quantity = floor($remaining_budget / $product->final_price);

            if ($max_quantity > 0) {
                // Check if adding this product gets us closer to target
                $product_total = $max_quantity * $product->final_price;

                // If we're close to target, try to optimize quantity
                if ($total_value + $product_total > $target_budget) {
                    $optimal_quantity = floor(($target_budget - $total_value) / $product->final_price);
                    if ($optimal_quantity > 0) {
                        $max_quantity = $optimal_quantity;
                        $product_total = $max_quantity * $product->final_price;
                    }
                }

                if ($max_quantity > 0) {
                    $selected_products[] = array(
                        'id' => $product->id,
                        'name' => $product->name,
                        'type' => $product->type,
                        'weight' => $product->weight,
                        'final_price' => $product->final_price,
                        'quantity' => $max_quantity,
                        'total_price' => $product_total
                    );

                    $total_value += $product_total;
                }
            }
        }

        return array(
            'products' => $selected_products,
            'total_value' => $total_value,
            'budget_used' => $total_value,
            'budget_remaining' => $budget - $total_value
        );
    }

    private function optimize_remaining_budget($current_products, $all_products, $remaining_budget)
    {
        $optimized_products = $current_products;
        $total_value = array_sum(array_column($current_products, 'total_price'));

        // Try to add more products within remaining budget
        foreach ($all_products as $product) {
            if ($product->final_price <= $remaining_budget) {
                $quantity = floor($remaining_budget / $product->final_price);

                if ($quantity > 0) {
                    // Check if product already exists in selection
                    $found = false;
                    foreach ($optimized_products as &$selected) {
                        if ($selected['id'] == $product->id) {
                            $selected['quantity'] += $quantity;
                            $selected['total_price'] += $quantity * $product->final_price;
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        $optimized_products[] = array(
                            'id' => $product->id,
                            'name' => $product->name,
                            'type' => $product->type,
                            'weight' => $product->weight,
                            'final_price' => $product->final_price,
                            'quantity' => $quantity,
                            'total_price' => $quantity * $product->final_price
                        );
                    }

                    $total_value += $quantity * $product->final_price;
                    $remaining_budget -= $quantity * $product->final_price;

                    if ($remaining_budget < 50) break; // Stop if less than 50 EUR remaining
                }
            }
        }

        return array(
            'products' => $optimized_products,
            'total_value' => $total_value
        );
    }

    private function extract_weight_value($weight_string)
    {
        // Extract numeric value from weight string (e.g., "10g" -> 10, "3.49g" -> 3.49)
        return floatval(preg_replace('/[^0-9.]/', '', $weight_string));
    }

    private function calculate_optimal_combination($products, $budget)
    {
        $combinations = array();

        // Sort products by price efficiency (price per gram)
        usort($products, function ($a, $b) {
            $weight_a = floatval(str_replace('g', '', $a->weight));
            $weight_b = floatval(str_replace('g', '', $b->weight));

            $efficiency_a = $a->final_price / $weight_a;
            $efficiency_b = $b->final_price / $weight_b;

            return $efficiency_a <=> $efficiency_b;
        });

        // Try different combinations to maximize budget usage
        $best_combination = array();
        $best_total = 0;

        // Simple greedy approach: try to fill budget with most efficient products
        foreach ($products as $product) {
            $remaining_budget = $budget - $best_total;
            $max_quantity = floor($remaining_budget / $product->final_price);

            if ($max_quantity > 0) {
                $product_total = $max_quantity * $product->final_price;

                if ($best_total + $product_total <= $budget) {
                    $best_combination[] = array(
                        'product' => $product,
                        'quantity' => $max_quantity,
                        'total_price' => $product_total
                    );
                    $best_total += $product_total;
                }
            }
        }

        // Ensure we don't go below 95% of budget if possible
        $min_budget = $budget * 0.95;
        if ($best_total < $min_budget) {
            // Try to add more products to reach minimum budget
            foreach ($products as $product) {
                $remaining_budget = $budget - $best_total;
                if ($remaining_budget >= $product->final_price) {
                    $additional_quantity = floor($remaining_budget / $product->final_price);
                    $additional_total = $additional_quantity * $product->final_price;

                    if ($best_total + $additional_total <= $budget) {
                        // Check if product already exists in combination
                        $found = false;
                        foreach ($best_combination as &$combo) {
                            if ($combo['product']->id == $product->id) {
                                $combo['quantity'] += $additional_quantity;
                                $combo['total_price'] += $additional_total;
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            $best_combination[] = array(
                                'product' => $product,
                                'quantity' => $additional_quantity,
                                'total_price' => $additional_total
                            );
                        }

                        $best_total += $additional_total;
                    }
                }
            }
        }

        return array(
            'combinations' => $best_combination,
            'total_price' => $best_total,
            'budget_used' => $best_total,
            'budget_remaining' => $budget - $best_total
        );
    }

    /**
     * Import products from CSV data string
     */
    public function import_products_from_csv($csv_content) {
        global $wpdb;
        
        // Parse CSV
        $lines = explode("\n", trim($csv_content));
        if (empty($lines)) {
            return array('success' => false, 'message' => 'No data found in CSV');
        }
        
        // Get headers from first line
        $headers = str_getcsv(array_shift($lines));
        
        $imported_count = 0;
        $updated_count = 0;
        $errors = array();
        
        foreach ($lines as $line_number => $line) {
            if (empty(trim($line))) continue;
            
            $data = str_getcsv($line);
            if (count($data) !== count($headers)) {
                $errors[] = "Line " . ($line_number + 2) . ": Column count mismatch";
                continue;
            }
            
            // Create associative array with headers
            $product_data = array_combine($headers, $data);
            
            // Skip if essential data is missing
            if (empty($product_data['name']) || empty($product_data['external_id'])) {
                continue;
            }
            
            // Map CSV data to database structure
            $mapped_data = $this->map_csv_to_database($product_data);
            
            // Check if product already exists by article_number
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->table_products} WHERE article_number = %s",
                $mapped_data['article_number']
            ));
            
            if ($existing) {
                // Update existing product
                $result = $wpdb->update(
                    $this->table_products,
                    $mapped_data,
                    array('id' => $existing)
                );
                if ($result !== false) {
                    $updated_count++;
                }
            } else {
                // Insert new product
                $result = $wpdb->insert($this->table_products, $mapped_data);
                if ($result) {
                    $imported_count++;
                }
            }
            
            if ($result === false) {
                $errors[] = "Failed to process: " . $product_data['name'];
            }
        }
        
        return array(
            'success' => true,
            'imported' => $imported_count,
            'updated' => $updated_count,
            'errors' => $errors
        );
    }
    
    /**
     * Map CSV fields to database fields
     */
    private function map_csv_to_database($csv_data) {
        // Determine product type from name
        $name = strtolower($csv_data['name']);
        $type = 'bar'; // default
        
        if (strpos($name, 'dukat') !== false || strpos($name, 'britannia') !== false || 
            strpos($name, 'philharmoniker') !== false || strpos($name, 'franc jozef') !== false) {
            $type = 'ducat';
        }
        
        // Extract weight from name
        $weight = $this->extract_weight_from_name($csv_data['name']);
        
        // Clean description HTML
        $description = strip_tags($csv_data['description']);
        if (strlen($description) > 1000) {
            $description = substr($description, 0, 997) . '...';
        }
        
        return array(
            'name' => sanitize_text_field($csv_data['name']),
            'description' => sanitize_textarea_field($description),
            'article_number' => sanitize_text_field($csv_data['external_id']),
            'type' => $type,
            'weight' => $weight,
            'price_net' => floatval($csv_data['price_avans'] ?: $csv_data['price']),
            'price_gross' => floatval($csv_data['price']),
            'buying_price' => floatval($csv_data['price_avans'] ?: $csv_data['price']),
            'selling_price' => floatval($csv_data['price']),
            'status' => ($csv_data['status'] === 'published') ? 'published' : 'draft',
            'stock_available' => intval($csv_data['avans_activate'] ?: 1),
            'advance_payment_available' => intval($csv_data['avans_activate'] ?: 1),
            'stock_markup_percent' => 4.0,
            'advance_discount_percent' => 3.0,
            'is_active' => 1,
            'is_demo' => 0
        );
    }
    
    /**
     * Extract weight from product name
     */
    private function extract_weight_from_name($name) {
        // Look for patterns like 1g, 20g, 1oz, 1kg, etc.
        if (preg_match('/(\d+(?:\.\d+)?)(g|oz|kg)\b/i', $name, $matches)) {
            $number = $matches[1];
            $unit = strtolower($matches[2]);
            
            // Convert to grams if needed
            switch ($unit) {
                case 'kg':
                    return ($number * 1000) . 'g';
                case 'oz':
                    return ($number * 31.1) . 'g';
                default:
                    return $number . $unit;
            }
        }
        
        // Look for patterns like 25x1g, 10x2g
        if (preg_match('/(\d+)x(\d+(?:\.\d+)?)(g|oz|kg)\b/i', $name, $matches)) {
            $quantity = $matches[1];
            $weight = $matches[2];
            $unit = strtolower($matches[3]);
            
            // Convert to grams if needed
            switch ($unit) {
                case 'kg':
                    $total_weight = ($quantity * $weight * 1000);
                    break;
                case 'oz':
                    $total_weight = ($quantity * $weight * 31.1);
                    break;
                default:
                    $total_weight = ($quantity * $weight);
                    break;
            }
            return $total_weight . 'g';
        }
        
        return '1g'; // default fallback
    }
    
    /**
     * Method to refresh default products (can be called manually)
     * This will replace old demo products with the new CSV products
     */
    public function refresh_default_products() {
        global $wpdb;
        
        // Remove old demo/sample products
        $wpdb->query("DELETE FROM {$this->table_products} WHERE is_demo = 1");
        
        // Insert new default products
        $this->insert_sample_products();
        
        return true;
    }
}
