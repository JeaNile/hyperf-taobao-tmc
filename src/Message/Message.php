<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc\Message;

class Message
{
    protected int $protocolVersion = 2;

    protected ?int $messageType;

    protected ?int $statusCode;

    protected ?int $statusPhrase;

    protected ?int $flag;

    protected ?string $token;

    protected array $content;

    public function __construct(
        $protocolVersion = 2,
        $messageType = null,
        $statusCode = null,
        $statusPhrase = null,
        $flag = null,
        $token = null,
        $content = null
    ) {
        $this->protocolVersion = $protocolVersion;
        $this->messageType = $messageType;
        $this->statusCode = $statusCode;
        $this->statusPhrase = $statusPhrase;
        $this->flag = $flag;
        $this->token = $token;
        $this->content = is_array($content) ? $content : [];
    }

    /**
     * 更新message中自定义的数据.
     */
    public function updateContent(array $otherContent)
    {
        if ($otherContent) {
            $this->content = array_merge($this->content, $otherContent);
        }
    }

    public function toArray(): array
    {
        return [
            'protocolVersion' => $this->protocolVersion,
            'messageType' => $this->messageType,
            'statusCode' => $this->statusCode,
            'statusPhrase' => $this->statusPhrase,
            'flag' => $this->flag,
            'token' => $this->token,
            'content' => $this->content,
        ];
    }

    public function toString()
    {
        return json_encode($this->toArray());
    }

    public function getProtocolVersion(): int
    {
        return $this->protocolVersion;
    }

    public function setProtocolVersion(int $protocolVersion)
    {
        $this->protocolVersion = $protocolVersion;
    }

    public function getMessageType()
    {
        return $this->messageType;
    }

    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusPhrase()
    {
        return $this->statusPhrase;
    }

    /**
     * @param null $statusPhrase
     */
    public function setStatusPhrase($statusPhrase)
    {
        $this->statusPhrase = $statusPhrase;
    }

    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * @param null $flag
     */
    public function setFlag($flag)
    {
        $this->flag = $flag;
    }

    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param null $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(?array $content)
    {
        $this->content = $content;
    }
}
