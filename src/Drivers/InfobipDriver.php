<?php


namespace RobustTools\SMS\Drivers;

use RobustTools\SMS\abstracts\Driver;
use RobustTools\SMS\Contracts\SMSServiceProviderDriverInterface;
use RobustTools\SMS\Exceptions\InternalServerErrorException;
use RobustTools\SMS\Exceptions\UnauthorizedException;
use RobustTools\SMS\Support\HTTPClient;

final class InfobipDriver extends Driver implements SMSServiceProviderDriverInterface
{
    /**
     * @var string|array
     */
    private $recipients;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $senderName;

    /**
     * @var string
     */
    private $endPoint;

    /**
     * InfobipDriver constructor.
     */
    public function __construct()
    {
        $this->username = config("resala.drivers.infobip.username");
        $this->password = config("resala.drivers.infobip.password");
        $this->senderName = config("resala.drivers.infobip.sender_name");
        $this->endPoint = config("resala.drivers.infobip.end_point");
    }

    /**
     * @param string|array $recipients
     * @return string|array
     */
    public function to($recipients)
    {
        return $this->recipients = $recipients;
    }

    /**
     * @param string $message
     * @return string
     */
    public function message(string $message): string
    {
        return $this->message = $message;
    }

    /**
     * Build Infobip request payload.
     *
     * @return string
     */
    public function payload(): string
    {
        return  json_encode([
            "text" => $this->message,
            "to" => $this->recipients,
            "from" => $this->senderName
        ]);
    }

    /**
     * Encode authorization credentials using base64.
     *
     * @return string
     */
    private function authorization()
    {
        return base64_encode($this->username . ':' . $this->password);
    }

    /**
     * Set Infobip Driver request headers.
     *
     * @return array|string[]
     */
    public function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . $this->authorization()
        ];
    }

    /**
     * @return string
     * @throws UnauthorizedException|InternalServerErrorException
     */
    public function send(): string
    {
        $response = (new HTTPClient())->post($this->endPoint, $this->headers(), $this->payload());

        return ($response->getstatusCode() == 200)
            ? "Message sent successfully"
            : "Message couldn't be sent";
    }
}
