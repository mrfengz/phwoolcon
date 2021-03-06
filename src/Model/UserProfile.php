<?php

namespace Phwoolcon\Model;

use Phwoolcon\Model;
use Phwoolcon\Text;

/**
 * Class UserProfile
 *
 * @package Phwoolcon\Model
 *
 * @method string getAvatar()
 * @method int getUserId()
 */
class UserProfile extends Model
{
    protected $_table = 'user_profile';
    protected $_pk = 'user_id';
    protected $_jsonFields = ['extra_data'];
    protected $extra_data = [];

    public function generateAvatarUrl($id = null, $path = 'uploads/avatar')
    {
        $id === null and $id = $this->getId();
        $subPath = substr(base62encode(crc32($id) + 62), 0, 2);
        $fileName = base62encode($id);
        $url = $path . '/' . $subPath . '/' . $fileName . '.png';
        return $url;
    }

    public function generateResetPasswordToken()
    {
        $token = $this->getUserId() . '-' . Text::random();
        $this->setExtraData('reset_password_token', $token);
        $this->setExtraData('reset_password_token_created_at', time());
        $this->save();
        return $token;
    }

    public function getExtraData($key = null, $default = null)
    {
        return $key === null ? $this->extra_data : fnGet($this->extra_data, $key, $default);
    }

    public function getResetPasswordToken()
    {
        return $this->getExtraData('reset_password_token');
    }

    public function getResetPasswordTokenCreatedAt()
    {
        return $this->getExtraData('reset_password_token_created_at');
    }

    public function initialize()
    {
        $this->belongsTo('user_id', User::class, 'id', ['alias' => 'user']);
        parent::initialize();
    }

    protected function prepareSave()
    {
        $this->getData('avatar') or $this->setData('avatar', $this->generateAvatarUrl());
        $this->extra_data or $this->extra_data = [];
        parent::prepareSave();
    }

    public function removeResetPasswordToken()
    {
        $this->setExtraData('reset_password_token');
        $this->setExtraData('reset_password_token_created_at');
        $this->save();
    }

    public function setExtraData($key, $value = null)
    {
        is_array($key) ? $this->extra_data = $key : array_set($this->extra_data, $key, $value);
        return $this;
    }
}
