<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Models\OrderConfirm;

class OrderConfirmMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:order-confirm-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and save order confirmation details from email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hostname = '{imap.hostinger.com:993/imap/ssl}INBOX'; 
        $username = 'tannu.mamnia@stepcoders.com'; 
        $password = 'Mamnia@123'; 

        $inbox = @imap_open($hostname, $username, $password);
        if (!$inbox) {
            $this->error('Could not connect to inbox.');
            return 1;
        }

        $emails = imap_search($inbox, 'FROM "praney.raghuvanshi@gmail.com"');

        if ($emails) {
            rsort($emails);
            $emailNumber = $emails[0];

            $overview = imap_fetch_overview($inbox, $emailNumber, 0);
            $structure = imap_fetchstructure($inbox, $emailNumber);
            $message = '';

            if (isset($structure->parts) && count($structure->parts)) {
                foreach ($structure->parts as $index => $part) {
                    if ($part->type == 0) { 
                        $message = imap_fetchbody($inbox, $emailNumber, $index + 1);
                        break;
                    }
                }
            } else {
                $message = imap_fetchbody($inbox, $emailNumber, 1);
            }

            $message = quoted_printable_decode($message);
            $message = htmlspecialchars_decode($message);

            $orderData = $this->extractOrderDetails($message);

            if (!empty($orderData)) {
                OrderConfirm::create($orderData);
                $this->info('Order details saved successfully.');
                return 0; 
            } else {
                $this->error('No valid order data found.');
                return 1; 
            }
        } else {
            $this->info('No new emails found.');
            return 0; 
        }
    }

    private function extractOrderDetails($message)
    {
        $orderData = [];
        $lines = explode("\n", $message);

        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos($line, 'Customer Name :') !== false) {
                $orderData['customer_name'] = $this->extractValue($line);
            }
            if (strpos($line, 'Customer Contact :') !== false) {
                $orderData['customer_contact'] = $this->extractValue($line);
            }
            if (strpos($line, 'Invoice') !== false) {
                $orderData['invoice_number'] = trim(str_replace('*', '', substr($line, strpos($line, ' ') + 1)));
            }
            if (strpos($line, 'Payment:') !== false) {
                $orderData['payment_mode'] = $this->extractValue($line);
            }
            if (strpos($line, 'Coach / Berth:') !== false) {
                $orderData['coach_berth'] = $this->extractValue($line);
            }
            if (strpos($line, 'Train:') !== false) {
                $orderData['train'] = $this->extractValue($line);
            }
            if (strpos($line, 'Delivery Station:') !== false) {
                $orderData['delivery_station'] = $this->extractValue($line);
            }
            if (strpos($line, 'Item Description') !== false) {
                continue; 
            }

            // Check for item details
            if (preg_match('/(\d+)\s+(.*)\s+(\d+)\s+([\d.]+)\s+([\d.]+)/', $line, $matches)) {
                $orderData['item_description'] = trim($matches[2]);
                $orderData['quantity'] = (int) trim($matches[3]);
                $orderData['price'] = (float) trim($matches[4]);
                $orderData['gst'] = (float) trim($matches[5]);
            }
            if (strpos($line, 'Total:') !== false) {
                $orderData['total'] = (float) trim($this->extractValue($line));
            }
        }

        return $orderData;
    }

    private function extractValue($line)
    {
        $start = strpos($line, ':') + 1;
        return trim(str_replace('*', '', substr($line, $start)));
    }
}


