<?php

namespace Davitig\Ima;

use Curl\Curl;
use Illuminate\Contracts\Config\Repository;

class Ima
{
    /**
     * The identifier for SMS transaction.
     *
     * @var string
     */
    const COMMAND_SMS = 'v';

    /**
     * The identifier for DMS authorization.
     *
     * @var string
     */
    const COMMAND_DMS_AUTH = 'a';

    /**
     * The identifier for DMS transaction.
     *
     * @var string
     */
    const COMMAND_DMS_EXEC = 't';

    /**
     * The identifier for recurring payment SMS transaction.
     *
     * @var string
     */
    const COMMAND_SMS_RP = 'z';

    /**
     * The identifier for recurring payment DMS authorization.
     *
     * @var string
     */
    const COMMAND_DMS_AUTH_RP = 'd';

    /**
     * The identifier for authorization on a certain amount and recurring payment registration.
     *
     * @var string
     */
    const COMMAND_REGISTER_RP = 'p';

    /**
     * The identifier for recurring payment.
     *
     * @var string
     */
    const COMMAND_RP = 'e';

    /**
     * The identifier for transaction result.
     *
     * @var string
     */
    const COMMAND_RESULT = 'c';

    /**
     * The identifier for transaction reversal.
     *
     * @var string
     */
    const COMMAND_REVERSE = 'r';

    /**
     * The identifier for transaction refund.
     *
     * @var string
     */
    const COMMAND_REFUND = 'k';

    /**
     * The identifier for credit transaction.
     *
     * @var string
     */
    const COMMAND_CREDIT = 'g';

    /**
     * The identifier to close the last opened batch for a particular merchant.
     *
     * @var string
     */
    const COMMAND_CLOSE = 'b';

    /**
     * The curl instance.
     *
     * @var \Curl\Curl
     */
    protected $curl;

    /**
     * The client handler url.
     *
     * @var string
     */
    protected $clientHandler;

    /**
     * Transaction currency code (ISO 4217), mandatory, (3 digits).
     *
     * @var string
     */
    protected $currency;

    /**
     * The language identifier.
     *
     * @var string
     */
    protected $language;

    /**
     * The client IP address.
     *
     * @var string
     */
    private $clientIpAddr;

    /**
     * An additional parameters for the transaction.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Create a new integrated merchant agent (IMA) instance.
     *
     * @param  \Curl\Curl $curl
     * @param  \Illuminate\Contracts\Config\Repository $config
     * @param  string|null $clientIpAddr
     * @param  string|null $language
     */
    public function __construct(Curl $curl, Repository $config, ?string $clientIpAddr, ?string $language = null)
    {
        $this->curl = $curl;
        $this->curl->setUrl($config->get('merchant_handler'));
        $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
        $this->curl->setOpt(CURLOPT_CAINFO, $certPath = $config->get('cert_path'));
        $this->curl->setOpt(CURLOPT_SSLCERT, $certPath);
        $this->curl->setOpt(CURLOPT_SSLKEY, $config->get('key_path'));
        $this->curl->setOpt(CURLOPT_SSLKEYPASSWD, $config->get('password'));

        $this->clientHandler = $config->get('client_handler');
        $this->currency = $config->get('currency');
        $this->clientIpAddr = $clientIpAddr;
        $this->language = $language;
    }

    /**
     * Get the curl instance.
     *
     * @return \Curl\curl
     */
    public function getCurl(): Curl
    {
        return $this->curl;
    }

    /**
     * Set the curl options.
     *
     * @param  array $options
     * @return $this
     */
    public function setCurlOpt(...$options): self
    {
        foreach ($options as $key => $value) {
            $this->curl->setOpt($key, $value);
        }

        return $this;
    }

    /**
     * Format the transaction amount in fractional units.
     *
     * @param  string $amount
     * @return string
     */
    public function formatAmount(string $amount): string
    {
        return number_format($amount, 2, '', '');
    }

    /**
     * Set the transaction currency code.
     *
     * @param  string $currency
     * @return $this
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get the client IP address.
     *
     * @return string
     */
    final public function getClientIpAddr(): string
    {
        return $this->clientIpAddr;
    }

    /**
     * Set the language identifier.
     *
     * @param  string $language
     * @return $this
     */
    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Add a parameters for the transaction.
     *
     * @param  array $parameters
     * @return $this
     */
    public function addParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Execute the transaction.
     *
     * @param  array $data
     * @param  string $method
     * @return \Davitig\Ima\Result
     */
    public function transaction(array $data, string $method = 'post'): Result
    {
        return new Result(
            $this->curl->$method(array_merge($this->parameters, $data)),
            $this->clientHandler
        );
    }

    /**
     * Start the single message system (SMS) transaction.
     *
     * @param  string $amount
     * @param  string|null $biller
     * @return \Davitig\Ima\Result
     */
    public function startSMSTrans(string $amount, ?string $biller = null): Result
    {
        $data = [];

        if (! is_null($biller)) {
            $data['biller'] = $biller;
        }

        return $this->transaction([
                'command'        => self::COMMAND_SMS,
                'amount'         => $this->formatAmount($amount),
                'currency'       => $this->currency,
                'client_ip_addr' => $this->clientIpAddr,
                'language'       => $this->language
            ] + $data);
    }

    /**
     * Start the dual message system (DMS) authorization.
     *
     * @param  string $amount
     * @return \Davitig\Ima\Result
     */
    public function startDMSAuth(string $amount): Result
    {
        return $this->transaction([
            'command'        => self::COMMAND_DMS_AUTH,
            'amount'         => $this->formatAmount($amount),
            'currency'       => $this->currency,
            'client_ip_addr' => $this->clientIpAddr,
        ]);
    }

    /**
     * Execute the dual message system (DMS) transaction.
     *
     * @param  string $transId
     * @param  string $amount
     * @return \Davitig\Ima\Result
     */
    public function makeDMSTrans(string $transId, string $amount): Result
    {
        return $this->transaction([
            'command'        => self::COMMAND_DMS_EXEC,
            'trans_id'       => $transId,
            'amount'         => $this->formatAmount($amount),
            'currency'       => $this->currency,
            'client_ip_addr' => $this->clientIpAddr,
        ]);
    }

    /**
     * Start the single message system (SMS) transaction for recurring payment.
     *
     * @param  string $amount
     * @param  string $expiry (MMYY)
     * @return \Davitig\Ima\Result
     */
    public function startSMSTransRP(string $amount, string $expiry): Result
    {
        return $this->startRP(self::COMMAND_SMS_RP, $amount, $expiry);
    }

    /**
     * Start the dual message system (DMS) authorization recurring payment.
     *
     * @param  string $amount
     * @param  string $expiry (MMYY)
     * @return \Davitig\Ima\Result
     */
    public function startDMSAuthRP(string $amount, string $expiry): Result
    {
        return $this->startRP(self::COMMAND_DMS_AUTH_RP, $amount, $expiry);
    }

    /**
     * Start recurring payment transaction.
     *
     * @param  string $command
     * @param  string $amount
     * @param  string $expiry (MMYY)
     * @return \Davitig\Ima\Result
     */
    protected function startRP(string $command, string $amount, string $expiry): Result
    {
        return $this->transaction([
            'command'          => $command,
            'amount'           => $this->formatAmount($amount),
            'currency'         => $this->currency,
            'client_ip_addr'   => $this->clientIpAddr,
            'language'         => $this->language,
            'perspayee_expiry' => $expiry
        ]);
    }

    /**
     * Execute the authorization on a certain amount and register a recurring payment.
     *
     * @param  string $expiry
     * @return \Davitig\Ima\Result
     */
    public function registerRP(string $expiry): Result
    {
        return $this->transaction([
            'command'          => self::COMMAND_REGISTER_RP,
            'currency'         => $this->currency,
            'client_ip_addr'   => $this->clientIpAddr,
            'language'         => $this->language,
            'perspayee_expiry' => $expiry
        ]);
    }

    /**
     * Execute the recurring payment.
     *
     * @param  string $amount
     * @return \Davitig\Ima\Result
     */
    public function makeRP(string $amount): Result
    {
        return $this->transaction([
            'command'         => self::COMMAND_RP,
            'amount'          => $this->formatAmount($amount),
            'currency'        => $this->currency,
            'client_ip_addr'  => $this->clientIpAddr,
            'language'        => $this->language
        ]);
    }

    /**
     * Execute the transaction result.
     *
     * @param  string $transId
     * @return \Davitig\Ima\Result
     */
    public function getTransResult(string $transId): Result
    {
        return $this->transaction([
            'command'        => self::COMMAND_RESULT,
            'trans_id'       => $transId,
            'client_ip_addr' => $this->clientIpAddr,
        ]);
    }

    /**
     * Execute the transaction reversal.
     *
     * @param  string $transId
     * @param  string|null $amount
     * @param  bool $fraud
     * @return \Davitig\Ima\Result
     */
    public function reverse(string $transId, ?string $amount = null, bool $fraud = false): Result
    {
        $data = [
            'command'  => self::COMMAND_REVERSE,
            'trans_id' => $transId
        ];

        if (! is_null($amount)) {
            $data['amount'] = $this->formatAmount($amount);
        }

        if ($fraud) {
            $data['fraud'] = 'yes';
        }

        return $this->transaction($data);
    }

    /**
     * Execute the transaction refund.
     *
     * @param  string $transId
     * @param  string|null $amount
     * @return \Davitig\Ima\Result
     */
    public function refund(string $transId, ?string $amount = null): Result
    {
        $data = [
            'command'  => self::COMMAND_REFUND,
            'trans_id' => $transId
        ];

        if (! is_null($amount)) {
            $data['amount'] = $this->formatAmount($amount);
        }

        return $this->transaction($data);
    }

    /**
     * Execute the credit transaction.
     *
     * @param  string $transId
     * @param  string|null $amount
     * @return \Davitig\Ima\Result
     */
    public function credit(string $transId, ?string $amount = null): Result
    {
        $data = [
            'command'  => self::COMMAND_CREDIT,
            'trans_id' => $transId
        ];

        if (! is_null($amount)) {
            $data['amount'] = $this->formatAmount($amount);
        }

        return $this->transaction($data);
    }

    /**
     * End the business day by closing the last opened batch for a particular merchant.
     *
     * @return \Davitig\Ima\Result
     */
    public function closeDay(): Result
    {
        return $this->transaction(['command' => self::COMMAND_CLOSE]);
    }
}
