<?php

namespace Stackstra\Curl;

use Stackstra\Types\Items;
use Closure;

class CurlResponseList
{
    /** @var CurlResponse[] */
    protected array $responses = [];

    /**
     * @param CurlResponse[] $responses
     */
    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

	public function add(CurlResponse $response)
	{
		$this->responses[] = $response;
	}

	/**
	 * @param CurlResponseList $responses
	 *
	 * @return void
	 */
	public function merge(CurlResponseList $responses)
	{
		foreach ($responses->get() as $response)
		{
			$this->add($response);
		}
	}

	public function get()
	{
		return $this->responses;
	}

	public function first(): ?CurlResponse
	{
		return Items::first($this->responses);
	}

	public function assertOK(): self
	{
		foreach ($this->responses as $response)
		{
			$response->assertOK();
		}

		return $this;
	}

	public function reset()
	{
		unset($this->responses);

		$this->responses = [];
	}

    public function hasErrors(): bool
    {
        foreach ($this->responses as $response)
        {
            if (!$response->isHttpCodeOK())
            {
                return true;
            }
        }

        return false;
    }

    public function map(Closure $closure): ?array
    {
        $array = [];

        foreach ($this->responses as $response)
        {
            $result = $closure($response);

            if ($result === null)
            {
                return null;
            }

            $array[] = $result;
        }

        return $array;
    }

    public function getContent($assoc = true): ?array
    {
        $result = [];

        foreach ($this->responses as $response)
        {
            if ($response->content === null)
            {
                return null;
            }

			     if ($assoc) { $result[$response->id] = $response->content; }
			else             { $result[]              = $response->content; }
        }

        return $result;
    }
}
