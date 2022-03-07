<?php

namespace Davitig\Ima;

use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class Result
{
    /**
     * The client handler url.
     *
     * @var string
     */
    protected $clientHandler;

    /**
     * The collection instance of the transaction result.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $result;

    /**
     * The raw result of the transaction.
     *
     * @var string
     */
    protected $rawResult;

    /**
     * Create a new result instance.
     *
     * @param  string $result
     * @param  string|null $clientHandler
     * @return void
     */
    public function __construct(string $result, string $clientHandler = null)
    {
        $this->clientHandler = $clientHandler;

        $this->setResult($result);
    }

    /**
     * Set the transaction result.
     *
     * @param  string $result
     * @return void
     */
    public function setResult(string $result): void
    {
        $this->rawResult = $result;

        $items = [];

        foreach (explode(PHP_EOL, $result) as $item) {
            if (trim($item) === '') {
                continue;
            }

            if (strpos($item, ':') === false) {
                $items['standalone_items'][] = $item;
            } else {
                [$key, $value] = explode(':', $item, 2);

                $items[strtolower(trim($key))] = trim($value);
            }
        }

        $this->result = new Collection($items);
    }

    /**
     * Get the transaction result collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getResult(): Collection
    {
        return $this->result;
    }

    /**
     * Get the transaction raw result.
     *
     * @return string
     */
    public function getRawResult(): string
    {
        return $this->rawResult;
    }

    /**
     * Get the transaction ID from the result.
     *
     * @return string|null
     */
    public function getTransId(): ?string
    {
        return $this->get('transaction_id');
    }

    /**
     * Determine if the transaction completed successfully.
     *
     * @return bool
     */
    public function success(): bool
    {
        return $this->get('result') == 'OK';
    }

    /**
     * Determine if the transaction failed.
     *
     * @return bool
     */
    public function failed(): bool
    {
        return $this->get('result') == 'FAILED';
    }

    /**
     * Determine if the transaction result is a warning.
     *
     * @return bool
     */
    public function isWarning(): bool
    {
        return ! is_null($this->getWarning());
    }

    /**
     * Get the warning result message.
     *
     * @return string
     */
    public function getWarning(): string
    {
        return $this->get('warning');
    }

    /**
     * Determine if the result has an error.
     *
     * @return bool
     */
    public function isError(): bool
    {
        return ! is_null($this->getError());
    }

    /**
     * Get the error result message.
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->get('error');
    }

    /**
     * Get an item from the result.
     *
     * @param  string $key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        return $this->result->get($key);
    }

    /**
     * Redirect to payment page.
     *
     * @param  string|null $transId
     * @param  bool $decode
     * @return \Illuminate\Support\HtmlString
     */
    public function redirectToPayment(string $transId = null, bool $decode = true): HtmlString
    {
        return Redirector::payment($this->clientHandler, $transId ?: $this->getTransId(), $decode);
    }

    /**
     * Convert the transaction result to its string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->result->toJson();
    }
}
