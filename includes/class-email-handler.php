<?php

class GCC_Email_Handler
{

    public function __construct()
    {
        // Set up email hooks if needed
    }

    public function send_quote_emails($quote_data, $ticket_number)
    {
        $owner_email_sent = $this->send_owner_notification($quote_data, $ticket_number);
        $customer_email_sent = $this->send_customer_confirmation($quote_data, $ticket_number);

        return $owner_email_sent && $customer_email_sent;
    }

    private function send_owner_notification($quote_data, $ticket_number)
    {
        $to = get_option('gcc_notification_email', get_option('admin_email'));
        $subject = 'Novi zahtev za ponudu - ' . $ticket_number;

        $message = $this->generate_owner_email_content($quote_data, $ticket_number);

        $site_domain = parse_url(get_site_url(), PHP_URL_HOST);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Gold Calculator <noreply@' . $site_domain . '>'
        );

        return wp_mail($to, $subject, $message, $headers);
    }

    private function send_customer_confirmation($quote_data, $ticket_number)
    {
        $to = $quote_data['email'];
        $subject = 'Potvrda zahteva za ponudu - ' . $ticket_number;

        $message = $this->generate_customer_email_content($quote_data, $ticket_number);

        $site_domain = parse_url(get_site_url(), PHP_URL_HOST);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Gold Calculator <noreply@' . $site_domain . '>'
        );

        return wp_mail($to, $subject, $message, $headers);
    }

    private function generate_owner_email_content($quote_data, $ticket_number)
    {
        $exchange_rate = get_option('gcc_exchange_rate', 117.5);
        $total_rsd = $quote_data['total_value'] * $exchange_rate;

        $products_html = '';
        if (!empty($quote_data['selected_products'])) {
            $products_html = '<h3>Odabrani proizvodi:</h3><ul>';
            foreach ($quote_data['selected_products'] as $product) {
                $quantity = isset($product['quantity']) ? $product['quantity'] : 1;
                $products_html .= sprintf(
                    '<li>%s (%s) - %d kom - €%.2f</li>',
                    $product['name'],
                    $product['weight'],
                    $quantity,
                    $product['final_price'] * $quantity
                );
            }
            $products_html .= '</ul>';
        }

        $delivery_method_text = $quote_data['delivery_method'] === 'stock' ? 'Sa stanja' : 'Avansna isplata';
        $product_type_text = $this->get_product_type_text($quote_data['product_type']);
        $weight_preference_text = $this->get_weight_preference_text($quote_data['weight_preference']);

        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Nova zahtev za ponudu</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #f4d03f; border-bottom: 2px solid #f4d03f; padding-bottom: 10px;">
                    Nova zahtev za ponudu
                </h2>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #2c3e50;">Informacije o klijentu</h3>
                    <p><strong>Broj tiketa:</strong> ' . $ticket_number . '</p>
                    <p><strong>Ime:</strong> ' . $quote_data['name'] . '</p>
                    <p><strong>Email:</strong> ' . $quote_data['email'] . '</p>
                    <p><strong>Telefon:</strong> ' . $quote_data['phone'] . '</p>
                    ' . (!empty($quote_data['message']) ? '<p><strong>Poruka:</strong> ' . nl2br($quote_data['message']) . '</p>' : '') . '
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #2c3e50;">Detalji zahteva</h3>
                    <p><strong>Budžet:</strong> ' . $quote_data['budget_range'] . '</p>
                    <p><strong>Tip proizvoda:</strong> ' . $product_type_text . '</p>
                    <p><strong>Način isporuke:</strong> ' . $delivery_method_text . '</p>
                    ' . (!empty($quote_data['weight_preference']) ? '<p><strong>Preferencija težine:</strong> ' . $weight_preference_text . '</p>' : '') . '
                    <p><strong>Tip zahteva:</strong> ' . ($quote_data['quote_type'] === 'email' ? 'Email ponuda' : 'Poziv trgovca') . '</p>
                </div>
                
                ' . $products_html . '
                
                <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <h3 style="margin-top: 0; color: #27ae60;">Ukupna vrednost</h3>
                    <p style="font-size: 18px; margin: 0;">
                        <strong>€' . number_format($quote_data['total_value'], 2) . '</strong>
                        <span style="color: #7f8c8d; font-size: 14px;">(' . number_format($total_rsd, 0) . ' RSD)</span>
                    </p>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #7f8c8d;">
                    <p>Ovaj email je automatski generisan od strane Gold Calculator Chatbot sistema.</p>
                    <p>Vreme zahteva: ' . date('d.m.Y H:i:s') . '</p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function generate_customer_email_content($quote_data, $ticket_number)
    {
        $template = get_option('gcc_email_template', 'Hvala na interesovanju za investiciono zlato. Uskoro ćemo Vam poslati detaljnu ponudu.');
        $exchange_rate = get_option('gcc_exchange_rate', 117.5);
        $total_rsd = $quote_data['total_value'] * $exchange_rate;
        $notification_email = get_option('gcc_notification_email', get_option('admin_email'));

        $products_html = '';
        if (!empty($quote_data['selected_products'])) {
            $products_html = '<h3>Vaš odabir:</h3><ul>';
            foreach ($quote_data['selected_products'] as $product) {
                $quantity = isset($product['quantity']) ? $product['quantity'] : 1;
                $products_html .= sprintf(
                    '<li>%s (%s) - %d kom</li>',
                    $product['name'],
                    $product['weight'],
                    $quantity
                );
            }
            $products_html .= '</ul>';
        }

        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Potvrda zahteva za ponudu</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h2 style="color: #f4d03f; margin-bottom: 10px;">Hvala na interesovanju!</h2>
                    <p style="color: #7f8c8d;">Vaš zahtev za ponudu je uspešno primljen</p>
                </div>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #2c3e50;">Detalji vašeg zahteva</h3>
                    <p><strong>Broj tiketa:</strong> ' . $ticket_number . '</p>
                    <p><strong>Budžet:</strong> ' . $quote_data['budget_range'] . '</p>
                    <p><strong>Ukupna vrednost:</strong> €' . number_format($quote_data['total_value'], 2) . ' (' . number_format($total_rsd, 0) . ' RSD)</p>
                </div>
                
                ' . $products_html . '
                
                <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <p style="margin: 0;">' . nl2br($template) . '</p>
                </div>
                
                <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <h4 style="margin-top: 0; color: #856404;">Sledeći koraci:</h4>
                    <ul style="margin-bottom: 0;">
                        <li>Naš tim će pregledati vaš zahtev</li>
                        <li>Pripremićemo detaljnu ponudu</li>
                        <li>Kontaktiraćemo vas u roku od 24 sata</li>
                    </ul>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <p style="color: #7f8c8d; font-size: 14px;">
                        Za hitna pitanja možete nas kontaktirati na:
                        <br>
                        <strong>Email:</strong> {$notification_email}
                        <br>
                        <strong>Telefon:</strong> +381 11 123 4567
                    </p>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #7f8c8d; text-align: center;">
                    <p>Ovaj email je automatski generisan od strane Gold Calculator sistema.</p>
                    <p>Molimo ne odgovarajte na ovaj email.</p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function get_product_type_text($type)
    {
        switch ($type) {
            case 'bars':
                return 'Samo poluge';
            case 'ducats':
                return 'Samo dukati';
            case 'combo':
                return 'Kombinacija poluga i dukata';
            default:
                return $type;
        }
    }

    private function get_weight_preference_text($preference)
    {
        switch ($preference) {
            case 'lighter':
                return 'Više lakših poluga';
            case 'heavier':
                return 'Manje težih poluga';
            default:
                return $preference;
        }
    }
}
