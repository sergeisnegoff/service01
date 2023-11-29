<?php


namespace App\Admin\User;


use Creonit\AdminBundle\Component\EditorComponent;

class NotificationEditor extends EditorComponent
{
    /**
     * @title Уведомление
     * @entity Notification
     *
     * @field system_title {required: true}
     * @field text {required: true}
     *
     * @template
     * {{ system_title | text | group('Системное название') }}
     * {{ text | text | group('Текст уведомления') }}
     * {{ code | text | group('Код', {notice: 'для разработчиков'}) }}
     */
    public function schema()
    {
    }
}
