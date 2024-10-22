<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\OrderConfirm;

class OrderConfirmController extends Controller
{
    public function index(Request $request)
    {
        $client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);
       $hostname = '{imap.hostinger.com:993/imap/ssl}INBOX'; 
        $username = 'tannu.mamnia@stepcoders.com'; 
        $password = 'Mamnia@123'; 
        $inbox = @imap_open($hostname, $username, $password);
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
                return view('orderconfirm', ['message' => 'Order details saved successfully.']);
            } else {
                return view('orderconfirm', ['message' => 'No valid order data found.']);
            }
        } else {
            return view('orderconfirm', ['message' => 'No new emails found.']);
        }
    }

    private function extractOrderDetails($message)
    {
        $orderData = [];
        $lines = explode("\n", $message);

        foreach ($lines as $line) {
            $line = trim($line);


        if (strpos($line, 'Customer Name :') !== false) {
            $start = strpos($line, ':') + 1; 
            $orderData['customer_name'] = trim(str_replace('*', '', substr($line, $start)));
        }
            if (strpos($line, 'Customer Contact :') !== false) {
                $orderData['customer_contact'] = trim(str_replace('*', '', substr($line, strpos($line, ':') + 1)));
            }
            if (strpos($line, 'Invoice') !== false) {
                $orderData['invoice_number'] = trim(str_replace('*', '', substr($line, strpos($line, ' ') + 1)));
            }
            if (strpos($line, 'Payment:') !== false) {
                $orderData['payment_mode'] = trim(str_replace('*', '', substr($line, strpos($line, ':') + 1)));
            }
            if (strpos($line, 'Coach / Berth:') !== false) {
                $orderData['coach_berth'] = trim(str_replace('*', '', substr($line, strpos($line, ':') + 1)));
            }
            if (strpos($line, 'Train:') !== false) {
                $orderData['train'] = trim(str_replace('*', '', substr($line, strpos($line, ':') + 1)));
            }
            if (strpos($line, 'Delivery Station:') !== false) {
                $orderData['delivery_station'] = trim(str_replace('*', '', substr($line, strpos($line, ':') + 1)));
            }
            if (strpos($line, 'Item Description') !== false) {
                continue;
            }
            if (preg_match('/(\d+)\s+(.*)\s+(\d+)\s+([\d.]+)\s+([\d.]+)/', $line, $matches)) {
                $orderData['item_description'] = trim($matches[2]);
                $orderData['quantity'] = (int) trim($matches[3]);
                $orderData['price'] = (float) trim($matches[4]);
                $orderData['gst'] = (float) trim($matches[5]);
            }
            if (strpos($line, 'Total:') !== false) {
                $orderData['total'] = (float) trim(str_replace('*', '', substr($line, strpos($line, ':') + 1)));
            }
        }

        return $orderData;
    }
}
