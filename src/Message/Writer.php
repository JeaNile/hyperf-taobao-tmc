<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc\Message;

use KkguanBe\HyperfTaobaoTmc\Constants\HeaderType;
use KkguanBe\HyperfTaobaoTmc\Constants\UNInteger;
use KkguanBe\HyperfTaobaoTmc\Constants\ValueFormat;

class Writer
{
    public static function write(Message $message): string
    {
        $buffer = new WriteBuffer();
        $buffer->byte($message->getProtocolVersion());
        $buffer->byte($message->getMessageType());

        if ($message->getToken() !== null) {
            $buffer->int16(HeaderType::TOKEN);
            $buffer->string($message->getToken());
        }

        if ($message->getContent()) {
            $content = $message->getContent();
            foreach ($content as $key => $value) {
                $buffer->int16(HeaderType::CUSTOM);
                $buffer->string($key);
                static::writeCustomValue($buffer, $value);
            }
        }

        if ($message->getStatusCode() !== null) {
            $buffer->int16(HeaderType::STATUS_CODE);
            $buffer->int32($message->getStatusCode());
        }

        if ($message->getStatusPhrase() !== null) {
            $buffer->int16(HeaderType::STATUS_PHRASE);
            $buffer->string($message->getStatusPhrase());
        }

        if ($message->getFlag() !== null) {
            $buffer->int16(HeaderType::FLAG);
            $buffer->int32($message->getFlag());
        }

        $buffer->int16(HeaderType::END_OF_HEADERS);

        return $buffer->getBuffer();
    }

    /**
     * @param mixed $value
     */
    private static function writeCustomValue(WriteBuffer $buffer, $value)
    {
        if (! $value) {
            $buffer->byte(ValueFormat::VOID);
        }

        if (! is_int($value) && ! is_long($value) && ! is_float($value)) {
            $buffer->byte(ValueFormat::COUNTED_STRING);
            $buffer->string($value);
        } else {
            if ($value < UNInteger::BYTE) {
                $buffer->byte(ValueFormat::BYTE);
                $buffer->byte($value);
            } elseif ($value < UNInteger::INT16) {
                $buffer->byte(ValueFormat::INT16);
                $buffer->int16($value);
            } elseif ($value < UNInteger::INT32) {
                $buffer->byte(ValueFormat::INT32);
                $buffer->int32($value);
            } elseif ($value < UNInteger::INT64) {
                if (PHP_VERSION_ID >= 50600) {
                    $buffer->byte(ValueFormat::INT64);
                    $buffer->int64($value);
                } else {
                    $buffer->byte(ValueFormat::COUNTED_STRING);
                    $buffer->string((string) $value);
                }
            } else {
                $buffer->byte(ValueFormat::COUNTED_STRING);
                $buffer->string($value);
            }
        }
    }
}
