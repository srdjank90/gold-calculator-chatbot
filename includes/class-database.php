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

        // Recreate table with new structure
        $charset_collate = $wpdb->get_charset_collate();

        $sql_products = "CREATE TABLE $this->table_products (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            weight int(11) NULL,
            price decimal(10,2) NOT NULL,
            price_avans decimal(10,2) NOT NULL,
            type enum('bar', 'ducat') DEFAULT 'bar',
            status enum('published', 'draft') DEFAULT 'draft',
            external_id int(11) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY external_id (external_id),
            KEY slug (slug),
            KEY status (status)
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
                'name' => 'Argor Heraeus 1g zlatna pločica',
                'slug' => 'argor-heraeus-1g-zlatna-plocica',
                'description' => '<p>C. Hafner 1g je čisto zlato u najmanjem nominalnom obliku. Zbog svoje veličine su <strong>jednostavne za čuvanje, imaju visoku likvidnost sa stanovi&scaron;ta naknadnog raspolaganja i savr&scaron;en su poklon</strong> za prijatelje i porodicu.&nbsp; Svaka pločica C. Hafner 1g, sa na&scaron;eg sajta <a href="https://zlatnistandard.rs/">Zlatni Standard</a>, prilikom kupovine dolazi u sigurnosnom plastičnom blister pakovanju, u kojem se nalaze svi relevantni sertifikati o proizvodu. U blister pakovanju C. Hafner 1 gram se takođe nalazi sertifikat koji sadrži potvrdu 999,9 finoće, dok se na samoj pločici i blisteru nalazi jedinstveni serijski broj proizvoda. Udruženje učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association ili LBMA&rdquo;) reguli&scaron;e i garantuje kvalitet proizvodnje i trgovine dragocenim metalima, uključujući i zlato, na svetskom nivou. Zbog svojih strogih uslova i ugleda koji ima, LBMA svojom sertifikacijom za Ugovorne strane su&scaron;tinski isključuje mogućnost manipulacije robom (na primer mogućnost da je gramaža manja ili da zlato nije ugovorene finoće i slično). Sa 1 gram čistog zlata, ova poluga je dobrodo&scaron;ao dodatak investicionim portfeljima koji teže likvidnosti i finansijskoj stabilnosti.&nbsp; <strong>LBMA Good Delivery garancija</strong> simbol je sigurnosti za kupce čiji je odabir C. Hafner 1g! Poluga C. Hafner 1 gram zahteva posebnu preciznost prilikom topljenja, oblikovanja i utiskivanja posebnih obeležja, &scaron;to upravo garantuju pouzdane rafinerije čiji se proizvodi nalaze u na&scaron;oj ponudi.&nbsp; <strong>Svojstva proizvoda</strong>: <strong>Oznaka 999,9 za finoću</strong> garantuje gotovo potpunu čistoću pločica C. Hafner od 1g. Oznaka za finoću, težina i logo su utisnuti na prednjoj strani, zajedno sa serijskim brojem. Svaka C. Hafner 1g pločica na sebi ima sve važne informacije, kao &scaron;to su <strong>oznaka težine, čistoća zlata i jedinstveni serijski broj</strong>.&nbsp;</p>',
                'weight' => 1,
                'price' => 12988.00,
                'price_avans' => 12666.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 2,
            ),
            array(
                'name' => 'C. Hafner 1g zlatna pločica',
                'slug' => 'c-hafner-1g-zlatna-plocica',
                'description' => '<p>C. Hafner 1g je čisto zlato u najmanjem nominalnom obliku. Zbog svoje veličine su <strong>jednostavne za čuvanje, imaju visoku likvidnost sa stanovi&scaron;ta naknadnog raspolaganja i savr&scaron;en su poklon</strong> za prijatelje i porodicu.&nbsp; Svaka pločica C. Hafner 1g, sa na&scaron;eg sajta <a href="https://zlatnistandard.rs/">Zlatni Standard</a>, prilikom kupovine dolazi u sigurnosnom plastičnom blister pakovanju, u kojem se nalaze svi relevantni sertifikati o proizvodu. U blister pakovanju C. Hafner 1 gram se takođe nalazi sertifikat koji sadrži potvrdu 999,9 finoće, dok se na samoj pločici i blisteru nalazi jedinstveni serijski broj proizvoda. Udruženje učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association ili LBMA&rdquo;) reguli&scaron;e i garantuje kvalitet proizvodnje i trgovine dragocenim metalima, uključujući i zlato, na svetskom nivou. Zbog svojih strogih uslova i ugleda koji ima, LBMA svojom sertifikacijom za Ugovorne strane su&scaron;tinski isključuje mogućnost manipulacije robom (na primer mogućnost da je gramaža manja ili da zlato nije ugovorene finoće i slično). Sa 1 gram čistog zlata, ova poluga je dobrodo&scaron;ao dodatak investicionim portfeljima koji teže likvidnosti i finansijskoj stabilnosti.&nbsp; <strong>LBMA Good Delivery garancija</strong> simbol je sigurnosti za kupce čiji je odabir C. Hafner 1g! Poluga C. Hafner 1 gram zahteva posebnu preciznost prilikom topljenja, oblikovanja i utiskivanja posebnih obeležja, &scaron;to upravo garantuju pouzdane rafinerije čiji se proizvodi nalaze u na&scaron;oj ponudi.&nbsp; <strong>Svojstva proizvoda</strong>: <strong>Oznaka 999,9 za finoću</strong> garantuje gotovo potpunu čistoću pločica C. Hafner od 1g. Oznaka za finoću, težina i logo su utisnuti na prednjoj strani, zajedno sa serijskim brojem. Svaka C. Hafner 1g pločica na sebi ima sve važne informacije, kao &scaron;to su <strong>oznaka težine, čistoća zlata i jedinstveni serijski broj</strong>.&nbsp;</p>',
                'weight' => 1,
                'price' => 12988.00,
                'price_avans' => 12666.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 1,
            ),
            array(
                'name' => 'The Royal Mint Britannia 25x1g zlatna pločica',
                'slug' => 'the-royal-mint-britannia-25x1g-zlatna-plocica',
                'description' => '<p>Zlatna poluga od 1 gram Britannia, simbol je britanske hrabrosti i istrajnosti, kroz jedinstven dizajn eminentnog britanskog dizajnera Džodi Klark. Sa 1 gram čistog zlata, ova poluga je <strong>dobrodo&scaron;ao dodatak investicionim portfeljima</strong> koji teže likvidnosti i finansijskoj stabilnosti; zbog svog cenovnog profila, pločica od 1 gram Britannia predstavlja dobar poklon za drage osobe za njihov poseban dan, jubilej ili drugu prigodu.&nbsp; Poluga je upakovana u <strong>sigurnosno pakovanje koje je brendirano Britannia</strong>, za bezbedan transport i skladi&scaron;tenje. Na pakovanju se nalazi providna, odstranjiva folija koja &scaron;titi blister tokom transporta.&nbsp; Može se skinuti nakon &scaron;to vam proizvod bude isporučen, bez uticaja na originalnu i utrživu vrednost proizvoda. <strong>Svi relevantni sertifikati </strong>se nalaze u sigurnosnom blister pakovanju u kojem se nalazi zlatna pločica. Pločica ima jedinstveni serijski broj.</p>',
                'weight' => 25,
                'price' => 311283.00,
                'price_avans' => 311283.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 240,
            ),
            array(
                'name' => 'Argor Heraeus 2g zlatna pločica',
                'slug' => 'argor-heraeus-2g-zlatna-plocica',
                'description' => '<p>2g Argor Heraeus je zagarantovano autentična pločica i dolazi u originalnom blister pakovanju, u kojem se nalaze svi relevantni sertifikati o proizvodu.&nbsp; Rafinerija koja proizvodi Argor Heraeus radi pod akreditacijom organizacije <strong>LBMA (London Bullion Market Association)</strong>. Udruženje učesnika trži&scaron;ta dragocenih metala jeste garant standardizovane proizvodnje u celom svetu, dok se uz njihovu Good delivery garanciju isključuje mogućnost manipulacije robom. Pouzdanost i vrednost proizvoda zagarantovani su <strong>vrhunskom čistoćom zlata od 999,9</strong>, dok su preciznom obradom ucrtane sve potrebne informacije na prednjoj strani poluge.&nbsp; 2g Argor Heraeus poluga može biti potpuno originalan dar najdražima za važan datum.</p>',
                'weight' => 2,
                'price' => 24796.00,
                'price_avans' => 24474.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 4,
            ),
            array(
                'name' => 'C. Hafner 2g zlatna pločica',
                'slug' => 'c-hafner-2g-zlatna-plocica',
                'description' => '<p>Pločica 2g C. Hafner brenda je zagarantovano autentična, jer dolazi u originalnom blister pakovanju, koje je pohranjeno svim relevantnim sertifikatima o proizvodu.&nbsp; Rafinerija koja proizvodi C. Hafner pločice od 2 grama radi pod akreditacijom organizacije <strong>LBMA (&ldquo;London Bullion Market Association&rdquo;)</strong>.&nbsp; Pouzdanost i vrednost proizvoda zagarantovani su <strong>vrhunskom čistoćom zlata od 999.9</strong>, dok su preciznom obradom ucrtane sve potrebne informacije na prednjoj strani poluge.</p>',
                'weight' => 2,
                'price' => 24796.00,
                'price_avans' => 24474.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 3,
            ),
            array(
                'name' => 'Argor Heraeus 5g zlatna pločica',
                'slug' => 'argor-heraeus-5g-zlatna-plocica',
                'description' => '<p>Investiciono zlato Argor Heraeus rafinerije <strong>precizno je izliveno u standardizovanim i kontrolisanim uslovima</strong>. Kontrolu procesa proizvodnje nadgleda Udruženja učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;). 5g Argor Heraeus pločica iz ponude <a href="https://zlatnistandard.rs/">Zlatnog standarda &ndash; prodavnice za kupovinu zlata</a>, <strong>spakovana je, prodaje se i dostavlja u originalnom plastičnom blister pakovanju</strong> i to zajedno sa sertifikatom o autentičnosti, atestom finoće 999,9 i Good delivery sertifikatom.&nbsp;&nbsp;&nbsp; Sve važne informacije kao &scaron;to su <strong>oznake za težinu, čistoću zlata i jedinstveni serijski broj</strong> poluge 5g Argor Heraeus brenda nalaze se na frontalnoj strani.</p>',
                'weight' => 5,
                'price' => 58393.00,
                'price_avans' => 57963.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 6,
            ),
            array(
                'name' => 'C.Hafner 5g zlatna pločica',
                'slug' => 'chafner-5g-zlatna-plocica',
                'description' => '<p>Zlatne pločice C. Hafner rafinerije <strong>precizno su izlivene u standardizovanim i kontrolisanim uslovima</strong>. Kontrolu procesa proizvodnje nadgleda Udruženja učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;). Poluga C. Hafner 5g finoće 999,9 iz ponude <a href="https://zlatnistandard.rs/">Zlatnog standarda &ndash; prodavnice za kupovinu zlata</a>, <strong>spakovana je, prodaje se i dostavlja u originalnom plastičnom blister pakovanju</strong> i to zajedno sa sertifikatom o autentičnosti.&nbsp;&nbsp; Sve važne informacije kao &scaron;to su <strong>oznake za težinu, čistoću zlata i jedinstveni serijski broj</strong> pločice 5 grama C. Hafner brenda nalaze se na frontalnoj strani.</p>',
                'weight' => 5,
                'price' => 58393.00,
                'price_avans' => 57963.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 5,
            ),
            array(
                'name' => 'The Royal Mint Britannia 25x5g zlatna pločica',
                'slug' => 'the-royal-mint-britannia-25x5g-zlatna-plocica',
                'description' => '<p>The Royal Mint od 5 grama Britannia, <strong>simbol je britanske hrabrosti i istrajnosti</strong>, kroz jedinstven dizajn Džodi Klark. Sa 5 grama čistog zlata, ova poluga je likvidan način da se osnaži u&scaron;teđevina ili investicioni portfelj lica koja teže finansijskoj stabilnosti i žele da sačuvaju kupovnu moć svog novca.&nbsp; Jedan od najpopularnijih formata u Srbiji, takođe <strong>pogodan za važan poklon</strong>, na primer za rođenje, kr&scaron;tenje, ili neki drugi specijalan jubilej. Poluga je upakovana u sigurnosno pakovanje koje je brendirano Britannia, za bezbedan transport i skladi&scaron;tenje.&nbsp; Na pakovanju se nalazi providna, odstranjiva folija koja &scaron;titi blister tokom transporta. Može se skinuti nakon &scaron;to vam proizvod bude isporučen, bez uticaja na originalnu i utrživu vrednost proizvoda. Svi relevantni sertifikati se nalaze u sigurnosnom blister pakovanju. Pločica ima jedinstveni serijski broj.</p>',
                'weight' => 125,
                'price' => 1428949.00,
                'price_avans' => 1428949.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 241,
            ),
            array(
                'name' => 'Argor Heraeus 10g zlatna pločica',
                'slug' => 'argor-heraeus-10g-zlatna-plocica',
                'description' => '<p>Pločice 10g Argor Heraeus rafinerije <strong>precizno su izlivene od starog zlata iz nakita, satova i medicinskih uređaja</strong>.&nbsp; Udruženje učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association ili LBMA&rdquo;) reguli&scaron;e i garantuje kvalitet proizvodnje i trgovine dragocenim metalima i <strong>dodeljuje Good Delivery</strong> sertifikat, kojim isključuje mogućnost manipulacije kada su u pitanju Argor Heraeus 10g pločice.&nbsp;&nbsp;&nbsp; Poluge Argor Heraeus rafinerije karakteri&scaron;e i <strong>odlična likvidnost</strong>. Proizvod kupljen u <a href="https://zlatnistandard.rs/">Zlatnom Standardu &ndash; prodavnici za kupovinu zlata</a>, dolazi upakovan u za&scaron;titnom <strong>plastičnom blisteru</strong>, sa svim sertifikatima autentičnosti.</p>',
                'weight' => 10,
                'price' => 115286.00,
                'price_avans' => 114213.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 8,
            ),
            array(
                'name' => 'C. Hafner 10g zlatna pločica',
                'slug' => 'c-hafner-10g-zlatna-plocica',
                'description' => '<p>Poluge 10 grama C. Hafner rafinerije <strong>precizno su izlivene od starog zlata iz nakita, satova i medicinskih uređaja</strong>.&nbsp; Udruženje učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association ili LBMA&rdquo;) reguli&scaron;e i garantuje kvalitet proizvodnje i trgovine dragocenim metalima<strong> i dodeljuje Good Delivery</strong> sertifikat, kojim isključuje mogućnost manipulacije kada je u pitanju zlatna poluga 10g.&nbsp;&nbsp;&nbsp; Investiciono zlato 10g C. Hafner rafinerije karakteri&scaron;e i <strong>odlična likvidnost</strong>. Proizvod kupljen u <a href="https://zlatnistandard.rs/">Zlatnom Standardu &ndash; prodavnici za kupovinu zlata</a>, dolazi upakovan u za&scaron;tritnom <strong>plastičnom blisteru</strong>, sa svim sertifikatima autentičnosti.</p>',
                'weight' => 10,
                'price' => 115286.00,
                'price_avans' => 114213.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 7,
            ),
            array(
                'name' => 'The Royal Mint Britannia 25x10g zlatna pločica',
                'slug' => 'the-royal-mint-britannia-25x10g-zlatna-plocica',
                'description' => '<p>Zlatna poluga od 10 grama Britannia, simbol je nepokolebljivog britanskog duha, kroz jedinstven dizajn Džodi Klark. Sa 10 grama čistog zlata, ova poluga je likvidan format i <strong>jedna od najpopularnijih gramaža</strong> među individualnim ulagačima u Srbiji.&nbsp; Poluga je upakovana u <strong>sigurnosno pakovanje koje je brendirano Britannia</strong>, za bezbedan transport i skladi&scaron;tenje. Na pakovanju se nalazi providna, odstranjiva folija koja &scaron;titi blister tokom transporta. Može se skinuti nakon &scaron;to vam proizvod bude isporučen, bez uticaja na originalnu i utrživu vrednost proizvoda. Svi relevantni sertifikati se nalaze u sigurnosnom blister pakovanju. Pločica ima jedinstveni serijski broj.</p>',
                'weight' => 250,
                'price' => 2801631.00,
                'price_avans' => 2801631.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 243,
            ),
            array(
                'name' => 'Argor Heraeus 20g zlatna pločica',
                'slug' => 'argor-heraeus-20g-zlatna-plocica',
                'description' => '<p>Investiciono zlato 20g Argor Heraeus rafinerije proizvodi se u <strong>standardizovanim uslovima koje kontroli&scaron;e </strong>Udruženja učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;) i dodeljuje LBMA Good Delivery sertifikat koji isključuje mogućnost manipulacije robom. Proizvod Argor Heraeus 20g dolazi u plastificiranom blister pakovanju, sa svim sertifikatima autentičnosti. Uz visoku likvidnost, Argor Heraeus 20g investiciono zlato <strong>jedno je od najtraženijih na trži&scaron;tu</strong>, dok se u okviru na&scaron;e <a href="https://zlatnistandard.rs/">Zlatni Standard</a> ponude možete snabdeti ovim proizvodima. Nudimo i najbolje otkupne cene zlata uz upotrebu najnovije tehnologije za analizu dragocenih metala.</p>',
                'weight' => 20,
                'price' => 228206.00,
                'price_avans' => 227133.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 11,
            ),
            array(
                'name' => 'C.Hafner 20g zlatna pločica',
                'slug' => 'chafner-20g-zlatna-plocica',
                'description' => '<p>Investiciono zlato 20g C. Hafner rafinerije proizvodi se u <strong>standardizovanim uslovima koje kontroli&scaron;e </strong>Udruženja učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;) i dodeljuje LBMA Good Delivery sertifikat koji isključuje mogućnost manipulacije robom. Proizvod C. Hafner 20g dolazi u plastificiranom blister pakovanju, sa svim sertifikatima autentičnosti. Uz visoku likvidnost, C. Hafner 20g investiciono zlato <strong>jedno je od najtraženijih na trži&scaron;tu</strong>, dok se u okviru na&scaron;e <a href="https://zlatnistandard.rs/">Zlatni Standard</a> ponude možete snabdeti ovim proizvodima. Nudimo i najbolje otkupne cene zlata uz upotrebu najnovije tehnologije za analizu dragocenih metala.</p>',
                'weight' => 20,
                'price' => 228206.00,
                'price_avans' => 227133.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 10,
            ),
            array(
                'name' => 'The Royal Mint Britannia 25x20g zlatna pločica',
                'slug' => 'the-royal-mint-britannia-25x20g-zlatna-plocica',
                'description' => '<p>Zlatna poluga od 20 grama Britannia, prikazuje snažnu sliku boginje Britannije kako drži svoj trident, kroz jedinstven dizajn Džodi Klark. Rimska boginja po kojoj je britanska nacija dobila ime, predstavlja večni simbol ponosa, snage i napretka zemlje.&nbsp; Sa 20 grama čistog zlata, ova poluga je <strong>najpopularniji vid ulaganja u investiciono zlato</strong> u Srbiji među individualnim ulagačima. Poluga je upakovana u sigurnosno pakovanje koje je brendirano Britannia, za bezbedan transport i skladi&scaron;tenje.&nbsp; Na pakovanju se nalazi providna, odstranjiva folija koja &scaron;titi blister tokom transporta. Može se skinuti nakon &scaron;to vam proizvod bude isporučen, bez uticaja na originalnu i utrživu vrednost proizvoda. <strong>Svi relevantni sertifikati</strong> se nalaze u sigurnosnom blister pakovanju. Poluga ima jedinstveni serijski broj.</p>',
                'weight' => 500,
                'price' => 5581705.00,
                'price_avans' => 5581705.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 242,
            ),
            array(
                'name' => 'Argor Heraeus 1 oz zlatna poluga',
                'slug' => 'argor-heraeus-1-oz-zlatna-poluga',
                'description' => '<p>1 unca Argor Heraeus rafinerije uz najbolje cene proizvoda na trži&scaron;tu. Svo investiciono zlato od 1 unce (31, 1g) i druge gramaže kod nas <strong>izrađeno je po najvi&scaron;im svetskim standarima nabavke zlatne rude i izrade finalnog proizvoda</strong>, a koje propisuje Udruženje učesnica trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;). Poluga Argor Heraeus 1 oz dolazi u prepoznatljivom i <strong>originalnom blister pakovanju</strong>, sa svim potrebnim sertifikatima za utrživanje proizvoda. <a href="https://zlatnistandard.rs/">Zlatni Standard</a> može biti pravi izbor za vas, ba&scaron; zato &scaron;to smo kompetentan sagovornik, zato &scaron;to imamo kontinuitet u snabdevanju i raspolažemo zalihama.</p>',
                'weight' => 31,
                'price' => 350897.00,
                'price_avans' => 349895.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 13,
            ),
            array(
                'name' => 'C.Hafner 1 oz zlatna poluga',
                'slug' => 'chafner-1-oz-zlatna-poluga',
                'description' => '<p>1 unca C. Hafner rafinerije uz najbolje cene proizvoda na trži&scaron;tu. Svo investiciono zlato od 1 unce (31,1g) i druge gramaže kod nas <strong>izrađeno je po najvi&scaron;im svetskim standarima nabavke zlatne rude i izrade finalnog proizvoda</strong>, a koje propisuje Udruženje učesnica trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;). Poluga C. Hafner 1 oz dolazi u prepoznatljivom i <strong>originalnom blister pakovanju</strong>, sa svim potrebnim sertifikatima za utrživanje proizvoda. <a href="https://zlatnistandard.rs/">Zlatni Standard</a> može biti pravi izbor za vas, ba&scaron; zato &scaron;to smo kompetentan sagovornik, zato &scaron;to imamo kontinuitet u snabdevanju i raspolažemo zalihama.</p>',
                'weight' => 31,
                'price' => 350897.00,
                'price_avans' => 349895.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 12,
            ),
            array(
                'name' => 'The Royal Mint Britannia 25x1oz zlatna poluga',
                'slug' => 'the-royal-mint-britannia-25x1oz-zlatna-poluga',
                'description' => '<p>Zlatna poluga od 1 unce Britannia, prikazuje nepogre&scaron;ivu inkarnaciju britanskog ponosa i hrabrosti: boginju Britanniju. <strong>Sa 31,1 grama čistog zlata</strong>, ova poluga predstavlja globalni standard za jedinicu težine zlata (jedna fina unca jednaka je 31,1 grama).&nbsp; Poluga je upakovana u <strong>sigurnosno pakovanje koje je brendirano Britannia</strong>, za bezbedan transport i skladi&scaron;tenje. Na pakovanju se nalazi providna, odstranjiva folija koja &scaron;titi blister tokom transporta. Može se skinuti nakon &scaron;to vam proizvod bude isporučen, bez uticaja na originalnu i utrživu vrednost proizvoda. Svi relevantni sertifikati se nalaze u sigurnosnom blister pakovanju. Poluga težine 1 unca ima jedinstveni serijski broj.</p>',
                'weight' => 775,
                'price' => 8638855.00,
                'price_avans' => 8638855.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 244,
            ),
            array(
                'name' => 'Argor Heraeus 50g zlatna poluga',
                'slug' => 'argor-heraeus-50g-zlatna-poluga',
                'description' => '<p>50g Argor Heraeus poluga nastaje standardizovanom proizvodnjom, čiju kontrolu obavlja organizacija Udruženje učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;). Argor Heraeus 50g zlato finoće od 999,9 nastaje kao produkt izrade u strogo kontrolisanim uslovima. Svaki proizvod 50g Argor Heraeus dolazi u <strong>jedinstvenom plastificiranom blister pakovanju, </strong>koje ima za&scaron;titnu ulogu i pohranjeno je LBMA Good Delivery sertifikatom, kao garantom da se proizvod može utržiti, kao i da nisu moguće manipulacije pri trgovini.</p>',
                'weight' => 50,
                'price' => 561396.00,
                'price_avans' => 561396.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 15,
            ),
            array(
                'name' => 'C.Hafner 50g zlatna poluga',
                'slug' => 'chafner-50g-zlatna-poluga',
                'description' => '<p>50g C. Hafner poluga nastaje standardizovanom proizvodnjom, čiju kontrolu obavlja organizacija Udruženje učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;). C. Hafner 50g zlato finoće 999,9 nastaje kao produkt izrade u strogo kontrolisanim uslovima. Svaki proizvod 50g C. Hafner dolazi u <strong>jedinstvenom plastificiranom blister pakovanju, </strong>koje ima za&scaron;tritnu ulogu i pohranjeno je LBMA Good Delivery sertifikatom, kao garantom da se proizvod može utržiti, kao i da nisu moguće manipulacije pri trgovini.</p>',
                'weight' => 50,
                'price' => 561396.00,
                'price_avans' => 561396.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 14,
            ),
            array(
                'name' => 'The Royal Mint Britannia 25x50g zlatna poluga',
                'slug' => 'the-royal-mint-britannia-25x50g-zlatna-poluga',
                'description' => '<p>Zlatna poluga od 50 grama Britannia, prikazuje boginju Britaniju sa trozupcem, kako gleda u daljinu preko morskih talasa i čuva obale britanskih ostrva. Atraktivan dizajn simboli&scaron;e snagu i otpornost, &scaron;to ovaj proizvod i donosi svakom ulagaču.&nbsp; <strong>Sa 50 grama čistog zlata</strong>, ova poluga predstavlja format ulaganja koji tipično biraju krupniji ulagači u investiciono zlato. Poluga je upakovana u sigurnosno pakovanje koje je brendirano Britannia, za bezbedan transport i skladi&scaron;tenje. Na pakovanju se nalazi providna, odstranjiva folija koja &scaron;titi blister tokom transporta. Može se skinuti nakon &scaron;to vam proizvod bude isporučen, bez uticaja na originalnu i utrživu vrednost proizvoda.&nbsp; <strong>Svi relevantni sertifikati</strong> se nalaze u sigurnosnom blisteru. Poluga ima jedinstveni serijski broj.</p>',
                'weight' => 1250,
                'price' => 13820217.00,
                'price_avans' => 13820217.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 245,
            ),
            array(
                'name' => 'Argor Heraeus 100g zlatna poluga',
                'slug' => 'argor-heraeus-100g-zlatna-poluga',
                'description' => '<p>Investiciono zlato 100g Argor Heraeus rafinerije nastaje standardizovanom proizvodnjom koju kontroli&scaron;e organizacija Udruženja učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;). Svaki Argor Heraeus 100g proizvod dolazi u <strong>jedinstvenom plastificiranom blister pakovanju</strong> sa svim sertifikatima kao garantima autentičnosti i LBMA Good Delivery sertifikatom koji pruža sigurnost od manipulacije ovom vrednom robom. Argor Heraeus 100g najvećeg kvaliteta poluge nastale pod unapred propisanim i standardizovanim uslovima mogu biti va&scaron;e jednostavnom kupovinom.</p>',
                'weight' => 100,
                'price' => 1114202.00,
                'price_avans' => 1114202.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 17,
            ),
            array(
                'name' => 'C.Hafner 100g zlatna poluga',
                'slug' => 'chafner-100g-zlatna-poluga',
                'description' => '<p>Investiciono zlato 100g C. Hafner rafinerije nastaje standardizovanom proizvodnjom koju kontroli&scaron;e organizacija Udruženja učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;). Svaki C. Hafner 100g proizvod dolazi u <strong>jedinstvenom plastificiranom blister pakovanju</strong> sa svim sertifikatima kao garantima autentičnosti i LBMA Good Delivery sertifikatom koji pruža sigurnost od manipulacije ovom vrednom robom. Hafner 100g najvećeg kvaliteta poluge nastale pod unapred propisanim i standardizovanim uslovima mogu biti va&scaron;e jednostavnom kupovinom.</p>',
                'weight' => 100,
                'price' => 1114202.00,
                'price_avans' => 1114202.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 16,
            ),
            array(
                'name' => 'The Royal Mint Britannia 25x100g zlatna poluga',
                'slug' => 'the-royal-mint-britannia-25x100g-zlatna-poluga',
                'description' => '<p>Zlatna poluga od 100 grama Britannia, prikazuje boginju Britaniju sa njenim trozupcem, stilizovanim morskim talasima u pozadini kao aluzijom na njenu neprestanu budnost i za&scaron;titu obala britanskih ostrva. Sa 100 grama čistog zlata, ova poluga predstavlja format ulaganja koji biraju krupniji ulagači.&nbsp; Ovaj format takođe daje mogućnost individualnim ulagačima da postignu jednu od najpovoljnijih cena po gramu zlata za konkretan likvidnosni profil. Poluga je upakovana u <strong>sigurnosno pakovanje koje je brendirano Britanijom</strong>, za bezbedan transport i skladi&scaron;tenje. Na pakovanju se nalazi providna, odstranjiva folija koja &scaron;titi blister tokom transporta.&nbsp; Može se skinuti nakon &scaron;to vam proizvod bude isporučen, bez uticaja na originalnu i utrživu vrednost proizvoda. <strong>Svi relevantni sertifikati</strong> se nalaze u sigurnosnom blister pakovanju. Poluga ima jedinstveni serijski broj.</p>',
                'weight' => 2500,
                'price' => 27506170.00,
                'price_avans' => 27506170.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 246,
            ),
            array(
                'name' => 'Zlatna poluga 250g C. Hafner',
                'slug' => 'zlatna-poluga-250g-c-hafner',
                'description' => '<p>Udruženje učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association ili LBMA&rdquo;) obavlja kontrolu proizvodnje i dodeljuje <strong>LBMA Good Delivery sertifikat</strong>, koja isključuje mogućnost manipulacije robom. Zlatne poluge od 250g pouzdane rafinerije iz Nemačke &ndash; C. Hafner, koja je ujedno porodična firma, već vi&scaron;e od 160 godina isporučuje zlatne proizvode od <strong>recikliranog zlata</strong> <strong>najvi&scaron;eg mogućeg kvaliteta</strong>. Zlatna poluga 250 grama rafinerije C. Hafner su proizvodi od čistog zlata finoće 999,9.&nbsp; <strong>Na prednjoj strani</strong> poluge od 250 grama nalazi se logo kompanije, godina osnivanja (1850), zemlja porekla (Nemačka), oznaka za finu težinu od 250 grama i fino zlato 999,9. Sa druge strane možete videti i serijski broj kao jo&scaron; jedan dokaz originalnosti. <strong>Potvrda o LBMA sertifikatu se nalazi na blisteru</strong>, autentičnom za&scaron;tritnom pakovanju u kome se nalazi zlatna poluga 250 grama. Imamo sjajan kontinuitet u snabdevanju, tako da zlatne poluge od 250 grama kod nas možete kupovati dugoročno uz najpovoljnije cene na trži&scaron;tu.</p>',
                'weight' => 250,
                'price' => 2755990.00,
                'price_avans' => 2755990.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 18,
            ),
            array(
                'name' => 'C. Hafner 500g zlatna poluga',
                'slug' => 'c-hafner-500g-zlatna-poluga',
                'description' => '<p>Zlatne poluge 500g rafinerije C. Hafner nastaju kao produkt porodične proizvodnje u Nemačkoj i to po standardizovanim uslovima koje kontroli&scaron;e <strong>Udruženje učesnika trži&scaron;ta dragocenih metala iz Londona</strong> (&ldquo;London Bullion Market Association ili LBMA&rdquo;). <strong>Good Delivery sertifikat</strong> organizacije LBMA znači da nema mogućnosti za ugovorne strane da dođe do manipulacije robom i ostvaruju se sigurni uslovi za utrživanje proizvoda, a poseduje ga i svaka zlatna poluga 500g iz <a href="https://zlatnistandard.rs/">Zlatni Standard</a> ponude. Hafner rafinerija je porodični biznis iz malog mesta u Nemačkoj, sa sopstvenom proizvodnjom i u skladu sa svim važećim standardima. Na prednjoj strani zlatne poluge 500 grama rafinerije C. Hafner možete videti logo kompanije, 1850. godinu kao godinu osnivanja, natpis zemlje porekla (Nemačka) i <strong>oznake za fino zlato najboljeg kvaliteta</strong>. Na poleđini se nalazi serijski broj i informacija o težini, dok se na plastičnom blister pakovanju može videti istaknut LBMA sertifikat.</p>',
                'weight' => 500,
                'price' => 5501241.00,
                'price_avans' => 5501241.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 19,
            ),
            array(
                'name' => 'C.Hafner zlatna poluga 1kg',
                'slug' => 'chafner-zlatna-poluga-1kg',
                'description' => '<p>Zlatna poluga kg rafinerije C. Hafner nastaje u strogo kontrolisanim uslovima kao produkt od čistog zlata.&nbsp; Porodični biznis u gradu Pforzheima u Nemačkoj i <strong>rafinerija C. Hafner ima preko 160 godina tradicije</strong> isporučivanja visokokvalitetnih zlatnih poluga nastalih u uslovima standardizovane proizvodnje. Udruženje učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association ili LBMA&rdquo;) reguli&scaron;e i garantuje kvalitet proizvodnje i kontroli&scaron;e nastanak zlatnih poluga od 1000g. Zlatna poluga kg isporučuje se u plastičnom, za&scaron;tritnom blisteru na kome se nalazi sertifikat proizvoda, finoće 999,9 sa jedinstvenim serijskim brojem. Ukoliko ste razmi&scaron;ljali da va&scaron;a opcija za kupovinu bude <strong>zlatna poluga 1kg cena</strong> je definitivno ne&scaron;to &scaron;to će vas zanimati. Slobodno nas kontaktirajte radi vi&scaron;e informacija.</p>',
                'weight' => 1000,
                'price' => 10970283.00,
                'price_avans' => 10970283.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 20,
            ),
            array(
                'name' => 'Mali dukat Franc Jozef – jednostruki',
                'slug' => 'mali-dukat-franc-jozef-jednostruki',
                'description' => '<p>Mali dukat Franc Jozef sa sobom nosi određeno istorijsko nasleđe i predstavlja simbol moći i elegancije. Ove kovanice su prvi put izrađene davne 1872. godine i do danas su ostale autentične uz male izmene. <strong>Austrijska kovnica </strong><strong>M&uuml;nze &Ouml;sterreich</strong> utiskuje i broj 1915, &scaron;to je godina pre smrti čuvenog Franca Josifa, vi&scaron;edecenijskog vladara Austro-Ugarske.&nbsp; Na gornjoj strani se nalazi <strong>lik Austrijskog cara Franca Jozefa</strong> i latinska izreka &rdquo;FRANC IOS I D G AVSTRIAE IMPERATOR (&ldquo;Po milosti Božijoj Austrijski car&rdquo;), dok je na donjoj strani utisnut simbol Autro-Ugarskog carskog orla sa dve glave, iznad kojih je kruna. Iznad orla je natpis &rdquo;HUNGAR BOHEM GAL LOD ILL REX A A 1915&rdquo; (skraćenice zemalja kojima je vladao). Pored izuzetnog istorijskog nasleđa koje nosi sa sobom, jednostruki mali dukat Franc Jozef može biti sjajan poklon dragim osobama.</p>',
                'price' => 41749.00,
                'price_avans' => 41565.00,
                'type' => 'ducat',
                'status' => 'published',
                'external_id' => 21,
            ),
            array(
                'name' => 'Veliki dukat Franc Jozef – četvorostruki',
                'slug' => 'veliki-dukat-franc-jozef-cetvorostruki',
                'description' => '<p>Dukat Franc Jozef veliki smatra se jednim od vrednijih novčića u Evropi, dok ga karakteri&scaron;e istorijsko nasleđe nekada&scaron;nje Austro-Ugarske monarhije i posebno lik Franca Jozefa, koji je 68 godina upravljao ovom nekada velesilom Evrope. Veliki dukat Franc Jozef na prednjoj strani, za razliku od jednostrukog novčića, a osim <strong>glave pokazuje i gornji deo tela vladara</strong>. Iznad njegove glave se nalazi natpis: &ldquo;FRANC IOS IDG AVSTRIAE IMPERATOR&rdquo;. Na zadnjoj strani se vidi <strong>Austrijski dvoglavi orao</strong> i natpis &ldquo;HUNGAR BOHEM GAL LOD ILL REX A A 1915&rdquo;. Četiri puta je teži od jednostruke verzije i takođe ga proizvodi renomirana kovnica <strong>M&uuml;nze &Ouml;sterreich.</strong> Dukat Franc Jozef 13.96gr smatra se izuzetno vrednim poklonom.&nbsp; Dukat Franc Jozef četvorostruki novčić može biti va&scaron; kupovinom online ili ako nas posetite na adresi Balkanska 2.&nbsp;</p>',
                'price' => 161838.00,
                'price_avans' => 161099.00,
                'type' => 'ducat',
                'status' => 'published',
                'external_id' => 22,
            ),
            array(
                'name' => 'Wiener Philharmoniker 1oz zlatni dukat',
                'slug' => 'wiener-philharmoniker-1oz-zlatni-dukat',
                'description' => '<p>Bečka Filharmonija dukat od 1 unce ili dukat Wiener Philharmoniker 1oz jeste <strong>novčić izrađen od 1 unce finog zlata 999,9</strong>. Moguće ga je koristiti kao sredstvo plaćanja i njegova nominalna vrednost iznosi 100 eura. U 12. veku ga je kreirao vojvoda Leopold V i <strong>predstavlja čuvenu Bečku filharmoniju</strong>, najpoznatiji i najpo&scaron;tovaniji simfonijski orkestar na svetu. <strong>Na zadnjoj strani dukata</strong> nalaze se instrumenti koji simboli&scaron;u Bečku filharmoniju, a iznad kojih je ispis na nemačkom jeziku &ldquo;WEINER PHILHARMONIKER&ldquo; (Bečka filharmonija). Dukat Bečka filharmonija <strong>sa prednje strane</strong> ima orgulje koncertne dvorane Musikverein u Beču, dok se iznad toga nalazi ispis na nemačkom jeziku &bdquo;REPUBLIK &Ouml;STERREICH&ldquo; (Republika Austrija). Ispod orgulja se nalaze informacije kao &scaron;to su težina, čistoća, godina kovanja i nominalna vrednost od 100 eura.</p>',
                'price' => 354235.00,
                'price_avans' => 353568.00,
                'type' => 'ducat',
                'status' => 'published',
                'external_id' => 23,
            ),
            array(
                'name' => 'C.Hafner smartpack zlatne pločice 10g (10x1g)',
                'slug' => 'chafner-zlatne-plocice-10g-10x1g',
                'description' => '<p><strong>Hafner zlatne pločice (10x1g) </strong>su odlična prilika da jednom kupovinom dobijete 10 zlatnih pločica od 1g. Sertifikovane zlatne poluge od 1g priznate su od Udruženja učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;) i dolaze sa LBMA Good Delivery sertifikatom. Porodična rafinerija C. Hafner već vi&scaron;e od 160 godina na trži&scaron;te plasira najkvalitenije proizvode i zlatne poluge od najfinijeg recikliranog zlata nastalog preradom zlatnih predmeta. Sve pločice u kompletu dolaze u za&scaron;tritnim <strong>plastičnim blister pakovanjima</strong>, sa svim sertifikatima autentičnosti.</p>',
                'weight' => 10,
                'price' => 125591.00,
                'price_avans' => 123444.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 239,
            ),
            array(
                'name' => 'C.Hafner smartpack zlatne pločice 20g (10x2g)',
                'slug' => 'chafner-zlatne-plocice-20g-10x2g',
                'description' => '<p><strong>Hafner zlatne pločice (10x2g) </strong>su odlična prilika da jednom kupovinom dobijete 10 zlatnih pločica od 2g. Sertifikovane zlatne poluge od 2g priznate su od Udruženja učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;) i dolaze sa LBMA Good Delivery sertifikatom. Porodična rafinerija C. Hafner već vi&scaron;e od 160 godina na trži&scaron;te plasira najkvalitenije proizvode i zlatne poluge od najfinijeg recikliranog zlata nastalog preradom zlatnih predmeta. Sve pločice u kompletu dolaze u za&scaron;tritnim <strong>plastičnim blister pakovanjima</strong>, sa svim sertifikatima autentičnosti.</p>',
                'weight' => 20,
                'price' => 243667.00,
                'price_avans' => 241520.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 9,
            ),
            array(
                'name' => 'Zlatni Standard 2g zlatna pločica za krštenje',
                'slug' => 'zlatna-plocica-za-krstenje-zlatni-standard',
                'description' => '<p>Zlatne pločice za kr&scaron;tenje od 2 grama precizno su izlivene od starog zlata iz nakita, satova i medicinskih uređaja. Udruženje učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association ili LBMA&rdquo;) <strong>reguli&scaron;e i garantuje kvalitet proizvodnje</strong> i trgovine dragocenim metalima i dodeljuje Good Delivery sertifikat. Zahvaljujući njemu, isključena je mogućnost manipulacije kada su u pitanju zlatne pločice za kr&scaron;tenje od 2g. <strong>Garant kupovine originalnog i vrednog poklona za kr&scaron;tenje</strong>. Zlatne pločice za kr&scaron;tenje karakteri&scaron;e i odlična likvidnost. Proizvod kupljen u <a href="https://zlatnistandard.rs/">Zlatnom Standardu &ndash; prodavnici za kupovinu zlata</a>, dolazi upakovan u za&scaron;tritnom plastičnom blisteru, sa svim sertifikatima autentičnosti. C. Hafner je <strong>jedna od najprestižnijih livnica i rafinerija u Evropi</strong>, osnovana 1850. godine u Nemačkoj i danas je jedan od vodećih LBMA akreditovanih igrača na polju dragocenih metala u svetu.</p>',
                'weight' => 2,
                'price' => 24963.00,
                'price_avans' => 24605.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 249,
            ),
            array(
                'name' => 'Zlatni Standard 2g zlatna pločica za poklon',
                'slug' => 'zlatna-plocica-za-poklon-zlatni-standard',
                'description' => '<p>Zlatna pločica za poklon 2g nastaje<strong> standardizovanom proizvodnjom</strong> koju kontroli&scaron;e organizacija Udruženja učesnika trži&scaron;ta dragocenih metala iz Londona (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;). Svaka zlatna pločica za poklon 2g dolazi u jedinstvenom plastificiranom blister pakovanju sa svim sertifikatima kao <strong>garantima autentičnosti i LBMA Good Delivery sertifikatom</strong> koji pruža sigurnost od manipulacije ovom vrednom robom. Zlatni Standard 2g zlatna pločica za poklon najvećeg kvaliteta nastaje pod unapred propisanim i standardizovanim uslovima i može biti va&scaron; jednostavnom kupovinom. <strong>Originalan, dragocen i vredan poklon za svaku priliku!</strong> C. Hafner je <strong>jedna od najprestižnijih livnica i rafinerija u Evropi</strong>, osnovana 1850. godine u Nemačkoj i danas je jedan od vodećih LBMA akreditovanih igrača na polju dragocenih metala u svetu.</p>',
                'weight' => 2,
                'price' => 25118.00,
                'price_avans' => 25225.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 247,
            ),
            array(
                'name' => 'Zlatni Standard zlatna pločica 2g za rođenje',
                'slug' => 'zlatna-plocica-za-rodenje-zlatni-standard',
                'description' => '<p>Zlatne pločice za rođenje precizno su izlivene u standardizovanim i kontrolisanim uslovima. Kontrolu procesa proizvodnje nadgleda <strong>Udruženja učesnika trži&scaron;ta dragocenih metala iz Londona</strong> (&ldquo;London Bullion Market Association&rdquo; ili &ldquo;LBMA&rdquo;). Pločice za rođenje 2g finoće 999,9 iz ponude <a href="https://zlatnistandard.rs/">Zlatnog standarda &ndash; prodavnice za kupovinu zlata</a>, spakovana je, prodaje se i dostavlja u <strong>originalnom plastičnom blister pakovanju</strong> i to zajedno sa sertifikatom o autentičnosti.&nbsp;&nbsp; Sve važne informacije kao &scaron;to su oznake za težinu, čistoću zlata i jedinstveni serijski broj pločice za rođenje 2 grama nalaze se na frontalnoj strani. C. Hafner je <strong>jedna od najprestižnijih livnica i rafinerija u Evropi</strong>, osnovana 1850. godine u Nemačkoj i danas je jedan od vodećih LBMA akreditovanih igrača na polju dragocenih metala u svetu.</p>',
                'weight' => 2,
                'price' => 25276.00,
                'price_avans' => 24829.00,
                'type' => 'bar',
                'status' => 'published',
                'external_id' => 248,
            ),
        );

        foreach ($sample_products as $product) {
            $wpdb->insert($this->table_products, $product);
        }
    }

    public function get_products_for_budget($budget, $type = 'all', $delivery_method = 'stock')
    {
        global $wpdb;

        // Convert EUR budget to RSD for comparison with product prices
        $exchange_rate = get_option('gcc_exchange_rate', 117.5);
        $budget_rsd = $budget * $exchange_rate;

        // Handle combo type - get both bars and ducats
        if ($type === 'combo') {
            $where_type = "AND type IN ('bar', 'ducat')";
            $query = "SELECT * FROM $this->table_products WHERE status = 'published' $where_type ORDER BY RAND(), price ASC";
            $products = $wpdb->get_results($query);
        } else if ($type !== 'all') {
            $query = "SELECT * FROM $this->table_products WHERE status = 'published' AND type = %s ORDER BY RAND(), price ASC";
            $products = $wpdb->get_results($wpdb->prepare($query, $type));
        } else {
            $query = "SELECT * FROM $this->table_products WHERE status = 'published' ORDER BY RAND(), price ASC";
            $products = $wpdb->get_results($query);
        }

        // Filter products that fit in budget and apply delivery method pricing
        $filtered_products = array();
        if ($products) {
            foreach ($products as $product) {
                // Choose correct price based on delivery method (price is in RSD)
                if ($delivery_method === 'advance') {
                    $final_price_rsd = $product->price_avans;
                } else {
                    $final_price_rsd = $product->price;
                }

                // Compare RSD price with RSD budget
                if ($final_price_rsd <= $budget_rsd) {
                    $product->final_price = $final_price_rsd;
                    $product->final_price_eur = $final_price_rsd / $exchange_rate; // For display purposes
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
            'slug' => sanitize_title($data['slug']),
            'description' => wp_kses_post($data['description']),
            'price' => floatval($data['price']),
            'price_avans' => floatval($data['price_avans']),
            'type' => floatval($data['type']),
            'status' => sanitize_text_field($data['status']),
            'external_id' => !empty($data['external_id']) ? intval($data['external_id']) : null
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
            'slug' => sanitize_title($data['slug']),
            'description' => wp_kses_post($data['description']),
            'price' => floatval($data['price']),
            'price_avans' => floatval($data['price_avans']),
            'type' => floatval($data['type']),
            'status' => sanitize_text_field($data['status']),
            'external_id' => !empty($data['external_id']) ? intval($data['external_id']) : null
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
                "WHERE name LIKE %s OR type LIKE %s OR type LIKE %s",
                '%' . $search . '%',
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }

        $valid_order_by = ['name', 'type', 'weight', 'price', 'price_avans', 'status', 'created_at'];
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
                "WHERE name LIKE %s OR type LIKE %s OR type LIKE %s",
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
                    array('value' => 'bars', 'label' => 'Više zlatnih poluga'),
                    array('value' => 'ducats', 'label' => 'Više zlatnih dukata'),
                    array('value' => 'combo', 'label' => 'Pola dukati, a pola poluge')
                )),
                'attributes' => json_encode(array('product_type' => true)),
                'question_order' => 2,
                'active' => 1,
                'condition_logic' => 'budget < 30000'
            ),
            array(
                'question' => 'Kakav procenat zlata želite?',
                'options' => json_encode(array(
                    array('value' => '50', 'label' => 'Pola Pola'),
                    array('value' => '33', 'label' => 'Više Dukata'),
                    array('value' => '67', 'label' => 'Više Poluga')
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
                'question' => 'Za veće investicije preporučujemo direktan razgovor sa trejderom. Šta želite da uradite?',
                'options' => json_encode(array(
                    array('value' => 'schedule', 'label' => 'Zakaži razgovor sa trejderom'),
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

        // Convert EUR budget to RSD for calculations
        $exchange_rate = get_option('gcc_exchange_rate', 117.5);
        $budget_rsd = $budget * $exchange_rate;

        // Get all available products
        $all_products = $this->get_all_available_products($product_type, $delivery_method);

        if (empty($all_products)) {
            return array(
                'products' => array(),
                'total_value' => 0,
                'total_value_eur' => 0,
                'budget_used' => 0,
                'budget_remaining' => $budget
            );
        }

        // Calculate optimal combination based on product type (pass RSD budget)
        if ($product_type === 'combo') {
            return $this->calculate_combo_combination($all_products, $budget_rsd, $combo_percentage, $budget, $exchange_rate);
        } else {
            return $this->calculate_single_type_combination($all_products, $budget_rsd, $product_type, $weight_preference, $budget, $exchange_rate);
        }
    }

    private function get_all_available_products($product_type, $delivery_method)
    {
        global $wpdb;

        $where_clauses = array(
            "status = 'published'",
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
        // if ($delivery_method === 'stock') {
        //     $where_clauses[] = "stock_available = 1";
        // } else {
        //     $where_clauses[] = "advance_payment_available = 1";
        // }

        $where_sql = implode(' AND ', $where_clauses);
        $query = "SELECT * FROM $this->table_products WHERE $where_sql ORDER BY type, price ASC";

        $products = $wpdb->get_results($query);

        // Apply correct pricing based on delivery method and add EUR conversion
        if ($products) {
            $exchange_rate = get_option('gcc_exchange_rate', 117.5);
            foreach ($products as $product) {
                if ($delivery_method === 'advance') {
                    $product->final_price = $product->price_avans; // RSD
                } else {
                    $product->final_price = $product->price; // RSD
                }
                $product->final_price_eur = $product->final_price / $exchange_rate; // For display
            }
        }

        return $products;
    }

    private function calculate_combo_combination($products, $budget_rsd, $combo_percentage, $budget_eur, $exchange_rate)
    {
        // Split RSD budget according to percentage
        $bars_budget_rsd = $budget_rsd * ($combo_percentage / 100);
        $ducats_budget_rsd = $budget_rsd * ((100 - $combo_percentage) / 100);

        // Separate products by type
        $bars = array_filter($products, function ($p) {
            return $p->type === 'bar';
        });
        $ducats = array_filter($products, function ($p) {
            return $p->type === 'ducat';
        });

        // Calculate optimal combination for each type (using RSD budgets)
        $bars_result = $this->calculate_single_type_combination($bars, $bars_budget_rsd, 'bars', '', $budget_eur * ($combo_percentage / 100), $exchange_rate);
        $ducats_result = $this->calculate_single_type_combination($ducats, $ducats_budget_rsd, 'ducats', '', $budget_eur * ((100 - $combo_percentage) / 100), $exchange_rate);

        // Combine results
        $combined_products = array_merge($bars_result['products'], $ducats_result['products']);
        $total_value_rsd = $bars_result['total_value'] + $ducats_result['total_value'];
        $total_value_eur = $total_value_rsd / $exchange_rate;

        // If we have remaining budget, try to optimize further
        $remaining_budget_rsd = $budget_rsd - $total_value_rsd;
        if ($remaining_budget_rsd > 0) {
            $optimized_result = $this->optimize_remaining_budget($combined_products, $products, $remaining_budget_rsd, $exchange_rate);
            $combined_products = $optimized_result['products'];
            $total_value_rsd = $optimized_result['total_value'];
            $total_value_eur = $total_value_rsd / $exchange_rate;
        }

        return array(
            'products' => $combined_products,
            'total_value' => $total_value_rsd,
            'total_value_eur' => $total_value_eur,
            'budget_used' => $total_value_eur,
            'budget_remaining' => $budget_eur - $total_value_eur
        );
    }

    private function calculate_single_type_combination($products, $budget_rsd, $product_type, $weight_preference, $budget_eur = null, $exchange_rate = null)
    {
        if (empty($products)) {
            // Set defaults for exchange rate if not provided
            if ($exchange_rate === null) {
                $exchange_rate = get_option('gcc_exchange_rate', 117.5);
            }
            if ($budget_eur === null) {
                $budget_eur = $budget_rsd / $exchange_rate;
            }

            return array(
                'products' => array(),
                'total_value' => 0,
                'total_value_eur' => 0,
                'budget_used' => 0,
                'budget_remaining' => $budget_eur
            );
        }

        // Apply weight preference sorting
        if ($weight_preference === 'lighter') {
            // Sort by weight ascending (lighter first)
            usort($products, function ($a, $b) {
                $weight_a = $a->weight;
                $weight_b = $b->weight;
                return $weight_a <=> $weight_b;
            });
        } elseif ($weight_preference === 'heavier') {
            // Sort by weight descending (heavier first)
            usort($products, function ($a, $b) {
                $weight_a = $a->weight;
                $weight_b = $b->weight;
                return $weight_b <=> $weight_a;
            });
        } else {
            // Default: sort by price efficiency (price per gram)
            usort($products, function ($a, $b) {
                $weight_a = isset($a->weight) ? $a->weight : 0;
                $weight_b = isset($b->weight) ? $b->weight : 0;

                // Always set final_price fallback
                if (!isset($a->final_price)) {
                    $a->final_price = isset($a->price) ? $a->price : 0;
                }
                if (!isset($b->final_price)) {
                    $b->final_price = isset($b->price) ? $b->price : 0;
                }

                // Prevent division by zero
                $efficiency_a = ($weight_a > 0) ? ($a->final_price / $weight_a) : PHP_FLOAT_MAX;
                $efficiency_b = ($weight_b > 0) ? ($b->final_price / $weight_b) : PHP_FLOAT_MAX;

                return $efficiency_a <=> $efficiency_b;
            });
        }

        // Set defaults for exchange rate if not provided
        if ($exchange_rate === null) {
            $exchange_rate = get_option('gcc_exchange_rate', 117.5);
        }
        if ($budget_eur === null) {
            $budget_eur = $budget_rsd / $exchange_rate;
        }

        // Use diverse product selection algorithm instead of greedy (all calculations in RSD)
        $selected_products = array();
        $total_value_rsd = 0;
        $target_budget_rsd = $budget_rsd * 0.95; // Target 95% of budget to leave room for variety

        // Add randomization to create different offers each time
        shuffle($products);

        // Limit to top products to create variety instead of using all
        $max_products_to_consider = min(count($products), 6);
        $products_to_use = array_slice($products, 0, $max_products_to_consider);

        foreach ($products_to_use as $product) {
            $remaining_budget_rsd = $budget_rsd - $total_value_rsd;

            // Skip if product is too expensive
            if ($product->final_price > $remaining_budget_rsd) {
                continue;
            }

            // Calculate how many of this product we can afford
            $max_quantity = floor($remaining_budget_rsd / $product->final_price);

            if ($max_quantity > 0) {
                // Use strategic quantity instead of maximum to create variety
                $strategic_quantity = $this->calculate_strategic_quantity_for_database($max_quantity, $remaining_budget_rsd, $product->final_price, count($products_to_use));
                $product_total = $strategic_quantity * $product->final_price;

                if ($strategic_quantity > 0) {
                    $selected_products[] = array(
                        'id' => $product->id,
                        'name' => $product->name,
                        'type' => $product->type,
                        'weight' => $product->weight,
                        'final_price' => $product->final_price,
                        'final_price_eur' => $product->final_price / $exchange_rate,
                        'quantity' => $strategic_quantity,
                        'total_price' => $product_total,
                        'total_price_eur' => $product_total / $exchange_rate
                    );

                    $total_value_rsd += $product_total;
                }
            }
        }

        $total_value_eur = $total_value_rsd / $exchange_rate;

        return array(
            'products' => $selected_products,
            'total_value' => $total_value_rsd,
            'total_value_eur' => $total_value_eur,
            'budget_used' => $total_value_eur,
            'budget_remaining' => $budget_eur - $total_value_eur
        );
    }

    /**
     * Calculate strategic quantity for database operations to create variety in offers
     */
    private function calculate_strategic_quantity_for_database($max_quantity, $remaining_budget, $item_price, $total_products)
    {
        // For expensive items (> 30% of remaining budget), limit to 1-3 pieces
        if ($item_price > ($remaining_budget * 0.3)) {
            return min($max_quantity, rand(1, 3));
        }

        // For medium items (10-30% of remaining budget), limit to 2-5 pieces
        if ($item_price > ($remaining_budget * 0.1)) {
            return min($max_quantity, rand(2, 5));
        }

        // For cheaper items, allow more but still limit for variety
        if ($total_products > 4) {
            // If we have many products, limit each to create more variety
            return min($max_quantity, rand(3, 10));
        } else {
            // If we have fewer products, allow more of each
            return min($max_quantity, rand(6, 15));
        }
    }

    private function optimize_remaining_budget($current_products, $all_products, $remaining_budget_rsd, $exchange_rate)
    {
        $optimized_products = $current_products;
        $total_value = array_sum(array_column($current_products, 'total_price'));

        // Try to add more products within remaining budget
        foreach ($all_products as $product) {
            if ($product->final_price <= $remaining_budget_rsd) {
                $quantity = floor($remaining_budget_rsd / $product->final_price);

                if ($quantity > 0) {
                    // Check if product already exists in selection
                    $found = false;
                    foreach ($optimized_products as &$selected) {
                        if ($selected['id'] == $product->id) {
                            $selected['quantity'] += $quantity;
                            $selected['total_price'] += $quantity * $product->final_price;
                            $selected['total_price_eur'] += $quantity * ($product->final_price / $exchange_rate);
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
                            'final_price_eur' => $product->final_price / $exchange_rate,
                            'quantity' => $quantity,
                            'total_price' => $quantity * $product->final_price,
                            'total_price_eur' => $quantity * ($product->final_price / $exchange_rate)
                        );
                    }

                    $total_value += $quantity * $product->final_price;
                    $remaining_budget_rsd -= $quantity * $product->final_price;

                    if ($remaining_budget_rsd < (50 * $exchange_rate)) break; // Stop if less than 50 EUR remaining
                }
            }
        }

        return array(
            'products' => $optimized_products,
            'total_value' => $total_value
        );
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
     * Synchronize product prices from external API
     */
    public function sync_product_prices()
    {
        error_log('GCC Price Sync: Starting product price synchronization');

        $api_url = get_option('gcc_api_url', '');
        if (empty($api_url)) {
            $error_message = 'No API URL configured';
            error_log('GCC Price Sync ERROR: ' . $error_message);
            update_option('gcc_last_sync_status', 'error');
            update_option('gcc_last_sync_message', $error_message);
            update_option('gcc_last_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        error_log('GCC Price Sync: Using API URL: ' . $api_url);

        // Fetch data from API
        error_log('GCC Price Sync: Making API request...');
        $response = wp_remote_get($api_url, array(
            'timeout' => 120,
            'headers' => array(
                'Accept' => 'application/json',
                'User-Agent' => 'WordPress/GCC-Plugin'
            )
        ));

        if (is_wp_error($response)) {
            $error_message = 'API request failed: ' . $response->get_error_message();
            $error_code = $response->get_error_code();
            error_log('GCC Price Sync ERROR: ' . $error_message . ' (Code: ' . $error_code . ')');
            update_option('gcc_last_sync_status', 'error');
            update_option('gcc_last_sync_message', $error_message);
            update_option('gcc_last_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $error_message = 'API returned HTTP ' . $response_code . ' status code';
            error_log('GCC Price Sync ERROR: ' . $error_message);
            update_option('gcc_last_sync_status', 'error');
            update_option('gcc_last_sync_message', $error_message);
            update_option('gcc_last_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        error_log('GCC Price Sync: API request successful (HTTP ' . $response_code . ')');

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            $error_message = 'Empty response body from API';
            error_log('GCC Price Sync ERROR: ' . $error_message);
            update_option('gcc_last_sync_status', 'error');
            update_option('gcc_last_sync_message', $error_message);
            update_option('gcc_last_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        error_log('GCC Price Sync: Response body length: ' . strlen($body) . ' characters');
        error_log('--------------');
        error_log($body);
        error_log('--------------');
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $json_error = json_last_error_msg();
            $error_message = 'Invalid JSON response from API: ' . $json_error;
            error_log('GCC Price Sync ERROR: ' . $error_message);
            error_log('GCC Price Sync ERROR: Response body (first 500 chars): ' . substr($body, 0, 500));
            update_option('gcc_last_sync_status', 'error');
            update_option('gcc_last_sync_message', $error_message);
            update_option('gcc_last_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        if (!is_array($data)) {
            $error_message = 'Expected array response from API, got: ' . gettype($data);
            error_log('GCC Price Sync ERROR: ' . $error_message);
            error_log('GCC Price Sync ERROR: Data content: ' . print_r($data, true));
            update_option('gcc_last_sync_status', 'error');
            update_option('gcc_last_sync_message', $error_message);
            update_option('gcc_last_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        if (empty($data)) {
            $error_message = 'Empty data array from API';
            error_log('GCC Price Sync ERROR: ' . $error_message);
            update_option('gcc_last_sync_status', 'error');
            update_option('gcc_last_sync_message', $error_message);
            update_option('gcc_last_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        error_log('GCC Price Sync: Processing ' . count($data) . ' products from API');

        global $wpdb;
        $updated_count = 0;
        $error_count = 0;
        $skipped_count = 0;
        $not_found_count = 0;

        foreach ($data as $index => $api_product) {
            if (!isset($api_product['product_id']) || !isset($api_product['selling_price']) || !isset($api_product['regular_price'])) {
                $missing_fields = [];
                if (!isset($api_product['product_id'])) $missing_fields[] = 'product_id';
                if (!isset($api_product['selling_price'])) $missing_fields[] = 'selling_price';
                if (!isset($api_product['regular_price'])) $missing_fields[] = 'regular_price';
                error_log('GCC Price Sync ERROR: Product at index ' . $index . ' missing required fields: ' . implode(', ', $missing_fields));
                error_log('GCC Price Sync ERROR: Product data: ' . print_r($api_product, true));
                $error_count++;
                continue;
            }

            $external_id = intval($api_product['product_id']);
            $selling_price = floatval($api_product['selling_price']);  // API price_avans = our selling_price
            $regular_price = floatval($api_product['regular_price']);        // API price = our regular_price
            $purchase_price = isset($api_product['purchase_price']) ? floatval($api_product['purchase_price']) : $selling_price;

            if ($external_id <= 0) {
                error_log('GCC Price Sync ERROR: Invalid product_id: ' . $api_product['product_id']);
                $error_count++;
                continue;
            }

            if ($selling_price <= 0 || $regular_price <= 0) {
                error_log('GCC Price Sync ERROR: Invalid prices for product ' . $external_id . ' - selling: ' . $selling_price . ', regular: ' . $regular_price);
                $error_count++;
                continue;
            }

            // Update product where external_id matches
            $result = $wpdb->update(
                $this->table_products,
                array(
                    'price' => $regular_price,           // API 'price' field
                    'price_avans' => $selling_price,     // API 'price_avans' field
                    'updated_at' => current_time('mysql')
                ),
                array('external_id' => $external_id),
                array('%f', '%f', '%s'),
                array('%d')
            );

            if ($result !== false && $result > 0) {
                $updated_count++;
                error_log("GCC Price Sync SUCCESS: Updated product external_id {$external_id} - regular_price: {$regular_price}, selling_price: {$selling_price}");
            } elseif ($result === false) {
                $wpdb_error = $wpdb->last_error ? $wpdb->last_error : 'Unknown database error';
                error_log("GCC Price Sync ERROR: Database update failed for product external_id {$external_id} - Error: {$wpdb_error}");
                $error_count++;
            } elseif ($result === 0) {
                // Check if product exists but no changes were needed or product not found
                $existing_product = $wpdb->get_row($wpdb->prepare("SELECT id, price, price_avans FROM {$this->table_products} WHERE external_id = %d", $external_id));
                if (!$existing_product) {
                    error_log("GCC Price Sync WARNING: Product with external_id {$external_id} not found in database");
                    $not_found_count++;
                } else {
                    error_log("GCC Price Sync INFO: No changes needed for product external_id {$external_id} (prices already current)");
                    $skipped_count++;
                }
            }
        }

        // Update sync information
        $current_time = current_time('timestamp');
        update_option('gcc_last_price_sync', $current_time);
        update_option('gcc_last_sync_time', date('Y-m-d H:i:s', $current_time));

        $status = ($error_count > 0) ? 'partial' : 'success';
        update_option('gcc_last_sync_status', $status);

        $message = "Price sync completed. Updated: {$updated_count} products";
        if ($skipped_count > 0) {
            $message .= ", Skipped (no changes): {$skipped_count}";
        }
        if ($not_found_count > 0) {
            $message .= ", Not found: {$not_found_count}";
        }
        if ($error_count > 0) {
            $message .= ", Errors: {$error_count}";
        }

        update_option('gcc_last_sync_message', $message);
        error_log('GCC Price Sync COMPLETED: ' . $message);

        return array(
            'success' => true,
            'message' => $message,
            'updated' => $updated_count,
            'errors' => $error_count
        );
    }

    /**
     * Get last price sync information
     */
    public function get_last_sync_info()
    {
        $last_sync = get_option('gcc_last_price_sync', 0);

        return array(
            'last_sync' => $last_sync,
            'last_sync_formatted' => $last_sync ? date('Y-m-d H:i:s', $last_sync) : 'Never',
            'time_ago' => $last_sync ? human_time_diff($last_sync, current_time('timestamp')) . ' ago' : 'Never'
        );
    }

    /**
     * Sync exchange rate from XML API
     */
    public function sync_exchange_rate()
    {
        error_log('GCC Exchange Rate Sync: Starting exchange rate synchronization');

        $api_url = 'https://radoviutoku.com/zs-xml';
        error_log('GCC Exchange Rate Sync: Using API URL: ' . $api_url);

        // Fetch data from API
        error_log('GCC Exchange Rate Sync: Making API request...');
        $response = wp_remote_get($api_url, array(
            'timeout' => 120,
            'headers' => array(
                'Accept' => 'application/json',
                'User-Agent' => 'WordPress/GCC-Plugin'
            )
        ));

        if (is_wp_error($response)) {
            $error_message = 'Exchange rate API request failed: ' . $response->get_error_message();
            $error_code = $response->get_error_code();
            error_log('GCC Exchange Rate Sync ERROR: ' . $error_message . ' (Code: ' . $error_code . ')');
            update_option('gcc_last_exchange_sync_status', 'error');
            update_option('gcc_last_exchange_sync_message', $error_message);
            update_option('gcc_last_exchange_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $error_message = 'Exchange rate API returned HTTP ' . $response_code . ' status code';
            error_log('GCC Exchange Rate Sync ERROR: ' . $error_message);
            update_option('gcc_last_exchange_sync_status', 'error');
            update_option('gcc_last_exchange_sync_message', $error_message);
            update_option('gcc_last_exchange_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        error_log('GCC Exchange Rate Sync: API request successful (HTTP ' . $response_code . ')');

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            $error_message = 'Empty response body from exchange rate API';
            error_log('GCC Exchange Rate Sync ERROR: ' . $error_message);
            update_option('gcc_last_exchange_sync_status', 'error');
            update_option('gcc_last_exchange_sync_message', $error_message);
            update_option('gcc_last_exchange_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        error_log('GCC Exchange Rate Sync: Response body length: ' . strlen($body) . ' characters');
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $json_error = json_last_error_msg();
            $error_message = 'Invalid JSON response from exchange rate API: ' . $json_error;
            error_log('GCC Exchange Rate Sync ERROR: ' . $error_message);
            error_log('GCC Exchange Rate Sync ERROR: Response body (first 500 chars): ' . substr($body, 0, 500));
            update_option('gcc_last_exchange_sync_status', 'error');
            update_option('gcc_last_exchange_sync_message', $error_message);
            update_option('gcc_last_exchange_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        if (!is_array($data)) {
            $error_message = 'Expected array response from exchange rate API, got: ' . gettype($data);
            error_log('GCC Exchange Rate Sync ERROR: ' . $error_message);
            error_log('GCC Exchange Rate Sync ERROR: Data content: ' . print_r($data, true));
            update_option('gcc_last_exchange_sync_status', 'error');
            update_option('gcc_last_exchange_sync_message', $error_message);
            update_option('gcc_last_exchange_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        error_log('GCC Exchange Rate Sync: Processing API response data');

        // Extract EUR/RSD rate from the nested structure
        $eur_rsd_rate = null;

        if (!isset($data['sale'])) {
            $error_message = 'Missing "sale" section in API response';
            error_log('GCC Exchange Rate Sync ERROR: ' . $error_message);
            error_log('GCC Exchange Rate Sync ERROR: Available keys: ' . implode(', ', array_keys($data)));
            update_option('gcc_last_exchange_sync_status', 'error');
            update_option('gcc_last_exchange_sync_message', $error_message);
            update_option('gcc_last_exchange_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        if (!isset($data['sale']['spot'])) {
            $error_message = 'Missing "spot" section in API response';
            error_log('GCC Exchange Rate Sync ERROR: ' . $error_message);
            error_log('GCC Exchange Rate Sync ERROR: Available sale keys: ' . implode(', ', array_keys($data['sale'])));
            update_option('gcc_last_exchange_sync_status', 'error');
            update_option('gcc_last_exchange_sync_message', $error_message);
            update_option('gcc_last_exchange_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        if (!isset($data['sale']['spot']['item']) || !is_array($data['sale']['spot']['item'])) {
            $error_message = 'Missing or invalid "item" array in API response';
            error_log('GCC Exchange Rate Sync ERROR: ' . $error_message);
            if (isset($data['sale']['spot'])) {
                error_log('GCC Exchange Rate Sync ERROR: Available spot keys: ' . implode(', ', array_keys($data['sale']['spot'])));
            }
            update_option('gcc_last_exchange_sync_status', 'error');
            update_option('gcc_last_exchange_sync_message', $error_message);
            update_option('gcc_last_exchange_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        error_log('GCC Exchange Rate Sync: Found ' . count($data['sale']['spot']['item']) . ' items to process');

        foreach ($data['sale']['spot']['item'] as $index => $item) {
            if (!isset($item['@attributes'])) {
                error_log('GCC Exchange Rate Sync WARNING: Item at index ' . $index . ' missing @attributes');
                continue;
            }

            if (!isset($item['@attributes']['name'])) {
                error_log('GCC Exchange Rate Sync WARNING: Item at index ' . $index . ' missing name attribute');
                continue;
            }

            $item_name = $item['@attributes']['name'];
            error_log('GCC Exchange Rate Sync: Processing item: ' . $item_name);

            if ($item_name === 'EURRSD') {
                if (!isset($item['@attributes']['value'])) {
                    error_log('GCC Exchange Rate Sync ERROR: EURRSD item missing value attribute');
                    continue;
                }

                $eur_rsd_rate = floatval($item['@attributes']['value']);
                error_log('GCC Exchange Rate Sync SUCCESS: Found EUR/RSD rate: ' . $eur_rsd_rate);
                break;
            }
        }

        if ($eur_rsd_rate === null || $eur_rsd_rate <= 0) {
            $error_message = 'EUR/RSD rate not found or invalid in API response';
            error_log('GCC Exchange Rate Sync ERROR: ' . $error_message . ' (rate: ' . $eur_rsd_rate . ')');
            update_option('gcc_last_exchange_sync_status', 'error');
            update_option('gcc_last_exchange_sync_message', $error_message);
            update_option('gcc_last_exchange_sync_time', date('Y-m-d H:i:s'));
            return array('success' => false, 'message' => $error_message);
        }

        // Update exchange rate settings
        $previous_rate = get_option('gcc_exchange_rate', 0);
        error_log('GCC Exchange Rate Sync: Updating rate from ' . $previous_rate . ' to ' . $eur_rsd_rate);

        $rate_updated = update_option('gcc_exchange_rate', $eur_rsd_rate);
        $display_updated = update_option('gcc_exchange_rate_display', 'EUR/RSD: ' . number_format($eur_rsd_rate, 2));

        if (!$rate_updated && get_option('gcc_exchange_rate') !== $eur_rsd_rate) {
            error_log('GCC Exchange Rate Sync ERROR: Failed to update exchange rate option');
        }

        if (!$display_updated && get_option('gcc_exchange_rate_display') !== ('EUR/RSD: ' . number_format($eur_rsd_rate, 2))) {
            error_log('GCC Exchange Rate Sync ERROR: Failed to update exchange rate display option');
        }

        // Update sync information
        $current_time = current_time('timestamp');
        update_option('gcc_last_exchange_sync', $current_time);
        update_option('gcc_last_exchange_sync_time', date('Y-m-d H:i:s', $current_time));
        update_option('gcc_last_exchange_sync_status', 'success');

        $rate_change = '';
        if ($previous_rate > 0) {
            $change = $eur_rsd_rate - $previous_rate;
            $change_percent = ($change / $previous_rate) * 100;
            $rate_change = sprintf(' (change: %+.4f, %+.2f%%)', $change, $change_percent);
        }

        $message = "Exchange rate updated to EUR/RSD: " . number_format($eur_rsd_rate, 4) . $rate_change;
        update_option('gcc_last_exchange_sync_message', $message);

        error_log('GCC Exchange Rate Sync COMPLETED: ' . $message);

        return array(
            'success' => true,
            'message' => $message,
            'rate' => $eur_rsd_rate
        );
    }
}
