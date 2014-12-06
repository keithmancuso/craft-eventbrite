<?php
namespace Craft;

class EventBrite_EventModel extends BaseModel
{
    protected function defineAttributes()
    {
        return array(
            'status' => AttributeType::String,
            'id' => AttributeType::String,
            'registerButton'=> AttributeType::String
        );
    }
}
