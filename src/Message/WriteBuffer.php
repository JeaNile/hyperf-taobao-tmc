<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc\Message;

class WriteBuffer
{
    private string $buffer = '';

    public function byte($byte)
    {
        $this->buffer .= bin2hex(pack('C', $byte));
    }

    public function int16($int16)
    {
        $this->buffer .= bin2hex(pack('v', $int16));
    }

    public function int32($int32)
    {
        $this->buffer .= bin2hex(pack('V', $int32));
    }

    public function int64($int64)
    {
        $this->buffer .= bin2hex(pack('P', $int64));
    }

    public function string($string)
    {
        if (strlen($string) > 0) {
            $this->int32(strlen($string));
            $this->buffer .= bin2hex(implode('', array_map('chr', unpack('C*', $string))));
        } else {
            $this->byte(0);
        }
    }

    public function getHexBuffer(): string
    {
        return $this->buffer;
    }

    public function getBuffer(): string
    {
        return hex2bin($this->buffer);
    }
}
