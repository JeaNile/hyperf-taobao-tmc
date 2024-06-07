<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc\Constants;

class MessageFields
{
    public const KIND = '__kind';

    // public final static String PULL_AMOUNT = "amount";

    public const CONFIRM_ID = 'id';

    public const CONFIRM_MSG = 'msg';

    public const CONFIRM_ATTACH_QUEUE = 'queue';

    public const DATA_DATAID = 'dataid';

    public const DATA_TOPIC = 'topic';

    public const DATA_CONTENT = 'content';

    public const DATA_PUBLISH_TIME = 'time';

    public const DATA_OUTGOING_PUBLISHER = 'publisher';

    public const DATA_OUTGOING_USER_NICK = 'nick';

    public const DATA_OUTGOING_USER_ID = 'userid';

    public const DATA_INCOMING_USER_SESSION = 'session';

    public const DATA_ATTACH_OUTGOING_TIME = 'outtime';

    public const OUTGOING_ID = 'id';

    // ATTACH means server will attch the filed to message, not passed from client
    // OUTGOING means only outgoing message to client have the field
    // INCOMING means only incoming message from client have the field
}
