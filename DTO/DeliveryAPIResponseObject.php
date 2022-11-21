<?php

namespace Hyvor\BlogsBundle\DTO;

class DeliveryAPIResponseObject
{
    public const TYPE_FILE = 'file';
    public const TYPE_REDIRECT = 'redirect';
    public const FILE_TYPE_TEMPLATE = 'template';

    /**
     * @var 'file'|'redirect'
     */
    public $type;

    public $at;

    /**
     * @var 'template'|'asset'|'media'
     */
    public $file_type;

    public $content;

    public $mime_type;

    // for redirect
    public $to;

    // for both
    public $cache;

    /**
     * @var int
     */
    public $status;

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): DeliveryAPIResponseObject
    {
        $obj = new self();
        $obj->type = $data['type'];
        $obj->at = $data['at'];
        $obj->cache = $data['cache'];
        $obj->status = $data['status'];

        if ($data['type'] === 'file') {
            $obj->file_type = $data['file_type'];
            $obj->content = $data['content'];
            $obj->mime_type = $data['mime_type'];
        } else {
            $obj->to = $data['to'];
        }

        return $obj;
    }

    public static function createFromJson(string $json): ?DeliveryAPIResponseObject
    {
        $json = json_decode($json, true);
        if (!is_array($json)) {
            return null;
        }

        return self::create($json);
    }
}
