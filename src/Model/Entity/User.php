<?php

namespace GintonicCMS\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Network\Email\Email;
use Cake\ORM\Entity;

class User extends Entity
{
    protected $_accessible = [
        'password' => false,
        'token' => false,
        '*' => true
    ];
    protected $_virtual = ['full_name'];
    protected $_hidden = ['password'];

    /**
     * TODO: doccomment
     */
    protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }

    /**
     * TODO: doccomment
     */
    protected function _getFullName()
    {
        if (isset($this->_properties['first']) && isset($this->_properties['last'])) {
            return $this->_properties['first'] . '  ' . $this->_properties['last'];
        }
        return false;
    }

    /**
     * TODO: doccomment
     */
    protected function _getFiles()
    {
        $files = TableRegistry::get('Files');
        $userFiles = $files->find('all')
            ->where(['user_id' => $this->id])
            ->all();
        return $userFiles;
    }

    /**
     * TODO: doccomment
     */
    public function sendRecovery()
    {
        $email = new Email('default');
        $email->viewVars([
            'userId' => $this->id,
            'token' => $this->token
        ]);
        $email->template('GintonicCMS.forgot_password')
            ->emailFormat('html')
            ->to($this->email)
            ->from([Configure::read('admin_mail') => Configure::read('site_name')])
            ->subject('Forgot Password');
        return $email->send();
    }

    /**
     * TODO: doccomment
     */
    public function sendSignup()
    {
        $email = new Email();
        $email->profile('default');
        $email->viewVars([
            'userId' => $this->id,
            'token' => $this->token
        ]);
        $email->template('GintonicCMS.signup')
            ->emailFormat('html')
            ->to($this->email)
            ->from(Configure::read('admin_mail'))
            ->subject('Account validation');
        return $email->send();
    }

    /**
     * TODO: doccomment
     */
    public function sendVerification()
    {
        $email = new Email('default');
        $email->viewVars([
            'userId' => $this->id,
            'token' => $this->token, 'userName' => $this->full_name
        ]);
        $email->template('GintonicCMS.resend_code')
            ->emailFormat('html')
            ->to($this->email)
            ->from([Configure::read('admin_mail') => Configure::read('site_name')])
            ->subject('Account validation');
        return $email->send();
    }

    /**
     * TODO: doccomment
     */
    public function updateToken()
    {
        $user->token = md5(uniqid(rand(), true));
        $user->token_creation = date("Y-m-d H:i:s");
    }

    /**
     * TODO: doccomment
     */
    public function verify($token)
    {
        $time = new Time($user->token_creation);
        if ($this->token == $token && $time->wasWithinLast('+1 day')) {
            $this->verified = true;
        }
        return $this->verified;
    }
}
