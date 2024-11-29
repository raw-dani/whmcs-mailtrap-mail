<?php

namespace WHMCS\Module\Mail;

/**
 * Mailtrap Mail Provider for WHMCS
 *
 * @author     Rohmat Ali Wardani
 * @copyright  2024
 * @license    MIT License
 * @version    1.0.0
 * @link       https://www.linkedin.com/in/rohmat-ali-wardani/
 */

use WHMCS\Authentication\CurrentUser;
use WHMCS\Exception\Mail\SendFailure;
use WHMCS\Mail\Message;
use WHMCS\Module\Contracts\SenderModuleInterface;
use WHMCS\Module\MailSender\DescriptionTrait;

class Mailtrap implements SenderModuleInterface
{
    use DescriptionTrait;

    private $apiUrl = 'https://send.api.mailtrap.io/api/send';
    private $author = 'Rohmat Ali Wardani';
    private $authorUrl = 'https://www.linkedin.com/in/rohmat-ali-wardani/';
    private $version = '1.0.0';

    public function settings()
    {
        return [
            'ApiToken' => [
                'FriendlyName' => 'API Token',
                'Type' => 'password',
                'Description' => 'Masukkan API Token dari Mailtrap',
            ],
            'from_email' => [
                'FriendlyName' => 'From Email',
                'Type' => 'text',
                'Description' => 'Alamat email pengirim',
            ],
            'from_name' => [
                'FriendlyName' => 'From Name',
                'Type' => 'text',
                'Description' => 'Nama pengirim',
            ],
            'author_info' => [
                'FriendlyName' => 'Developer Info',
                'Type' => 'readonly',
                'Description' => 'Developed by <a href="' . $this->authorUrl . '" target="_blank">' . $this->author . '</a>',
            ],
        ];
    }

    public function getName()
    {
        return 'Mailtrap';
    }

    public function getDisplayName()
    {
        return 'Mailtrap Mail Provider by ' . $this->author;
    }

    public function getAuthorInfo()
    {
        return [
            'name' => $this->author,
            'url' => $this->authorUrl,
            'version' => $this->version
        ];
    }

    private function sendRequest($endpoint, $data, $apiToken)
    {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiToken,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL Error: $error");
        }

        if ($httpCode >= 400) {
            $responseData = json_decode($response, true);
            throw new \Exception(isset($responseData['message']) ? $responseData['message'] : "HTTP Error: $httpCode");
        }

        return json_decode($response, true);
    }

    public function testConnection(array $settings)
    {
        $currentAdmin = (new CurrentUser)->admin();

        try {
            $data = [
                'from' => [
                    'name' => $settings['from_name'],
                    'email' => $settings['from_email']
                ],
                'to' => [
                    ['email' => $currentAdmin->email]
                ],
                'subject' => 'Mailtrap Test Connection',
                'html' => 'Email ini dikirim untuk mengetes konfigurasi mail provider Mailtrap.<br><br>' .
                         '<small>Powered by Mailtrap Mail Provider<br>' .
                         'Developed by <a href="' . $this->authorUrl . '">' . $this->author . '</a></small>'
            ];

            $this->sendRequest($this->apiUrl, $data, $settings['ApiToken']);

            return [
                'success' => true,
                'message' => 'Koneksi berhasil dan email test terkirim!'
            ];
        } catch (\Exception $e) {
            error_log('Mailtrap API Error: ' . $e->getMessage());
            throw new \Exception("Gagal melakukan test koneksi: " . $e->getMessage());
        }
    }

    public function send(array $settings, Message $message)
    {
        try {
            $data = [
                'from' => [
                    'name' => $settings['from_name'],
                    'email' => $settings['from_email']
                ],
                'to' => [],
                'subject' => $message->getSubject()
            ];

            // Set recipients
            foreach ($message->getRecipients('to') as $to) {
                $data['to'][] = ['email' => $to[0], 'name' => $to[1]];
            }

            // Set CC
            if ($cc = $message->getRecipients('cc')) {
                $data['cc'] = array_map(function($recipient) {
                    return ['email' => $recipient[0], 'name' => $recipient[1]];
                }, $cc);
            }

            // Set BCC
            if ($bcc = $message->getRecipients('bcc')) {
                $data['bcc'] = array_map(function($recipient) {
                    return ['email' => $recipient[0], 'name' => $recipient[1]];
                }, $bcc);
            }

            // Set content
            $body = $message->getBody();
            if ($body) {
                $branding = '<br><br><small style="color:#666;">Powered by Mailtrap Mail Provider<br>' .
                           'Developed by <a href="' . $this->authorUrl . '">' . $this->author . '</a></small>';
                $data['html'] = $body . $branding;
                $data['text'] = $message->getPlainText() ?: strip_tags($body);
            } else {
                $data['text'] = $message->getPlainText();
            }

            // Set Reply-To
            if ($replyTo = $message->getReplyTo()) {
                $data['replyTo'] = [
                    'email' => $replyTo['email'],
                    'name' => $replyTo['name']
                ];
            }

            // Set attachments
            if ($attachments = $message->getAttachments()) {
                $data['attachments'] = [];
                foreach ($attachments as $attachment) {
                    if (isset($attachment['data'])) {
                        $content = base64_encode($attachment['data']);
                    } else {
                        $content = base64_encode(file_get_contents($attachment['filepath']));
                    }
                    $data['attachments'][] = [
                        'filename' => $attachment['filename'],
                        'content' => $content
                    ];
                }
            }

            $this->sendRequest($this->apiUrl, $data, $settings['ApiToken']);

        } catch (\Exception $e) {
            error_log('Mailtrap Send Error: ' . $e->getMessage());
            throw new SendFailure("Gagal mengirim email: " . $e->getMessage());
        }
    }
}