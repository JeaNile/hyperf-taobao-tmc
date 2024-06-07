<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc\Message;

use KkguanBe\HyperfTaobaoTmc\Constants\HeaderType;
use KkguanBe\HyperfTaobaoTmc\Constants\ValueFormat;

class Reader
{
    public static function read($stream): Message
    {
        $message = new Message();

        $message->setProtocolVersion(ord($stream[0]));
        $message->setMessageType(ord($stream[1]));

        $headerType = unpack('v', substr($stream, 2, 2))[1];
        $index = 4;
        while ($headerType != HeaderType::END_OF_HEADERS) {
            if ($headerType === HeaderType::STATUS_CODE) {
                $message->setStatusCode(unpack('V', substr($stream, $index, 4))[1]);
                $index += 4;
            } elseif ($headerType === HeaderType::STATUS_PHRASE) {
                $length = unpack('V', substr($stream, $index, 4))[1];
                $message->setStatusPhrase(unpack('V', substr($stream, $index, 4))[1]);
                $index = $index + $length + 4;
            } elseif ($headerType === HeaderType::FLAG) {
                $message->setFlag(unpack('V', substr($stream, $index, 4))[1]);
                $index += 4;
            } elseif ($headerType === HeaderType::TOKEN) {
                $length = unpack('V', substr($stream, $index, 4))[1];
                $message->setToken(substr($stream, $index + 4, $length));
                $index = $index + $length + 4;
            } elseif ($headerType === HeaderType::CUSTOM) {
                $length = unpack('V', substr($stream, $index, 4))[1];
                $key = substr($stream, $index + 4, $length);
                $index = $index + $length + 4;

                $format = ord($stream[$index]);
                ++$index;
                if ($format === ValueFormat::INT64) {
                    $high = unpack('V', substr($stream, $index + 4, 4))[1];
                    $low = unpack('V', substr($stream, $index, 4))[1];
                    $str = '';
                    $radix = 10;
                    while (1) {
                        $mod = ($high % $radix) * 4294967296 + $low;
                        $high = floor($high / $radix);
                        $low = floor($mod / $radix);
                        $str = ($mod % $radix) . $str;
                        if (! $high && ! $low) {
                            break;
                        }
                    }
                    $message->updateContent([$key => $str]);
                    $index += 8;
                } elseif ($format === ValueFormat::DATE) {
                    $message->updateContent([$key => unpack('V', substr($stream, $index, 4))[1] + unpack('V', substr($stream, $index + 4, 4))[1] * 4294967296]);
                    $index += 8;
                } elseif ($format === ValueFormat::COUNTED_STRING) {
                    $length = unpack('V', substr($stream, $index, 4))[1];
                    $message->updateContent([$key => substr($stream, $index + 4, $length)]);
                    $index = $index + $length + 4;
                } elseif ($format === ValueFormat::BYTE) {
                    $message->updateContent([$key => ord($stream[$index])]);
                    ++$index;
                } elseif ($format === ValueFormat::INT32) {
                    $message->updateContent([$key => unpack('V', substr($stream, $index, 4))[1]]);
                    $index += 4;
                } elseif ($format === ValueFormat::INT16) {
                    $message->updateContent([$key => unpack('v', substr($stream, $index, 2))[1]]);
                    $index += 2;
                }
            }
            $headerType = unpack('v', substr($stream, $index, 2))[1];
            $index += 2;
        }

        return $message;
    }
}
