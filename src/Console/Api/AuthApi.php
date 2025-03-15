<?php

namespace Happyphper\Dify\Console\Api;

use Happyphper\Dify\Console\ConsoleClient;

class AuthApi
{
    /**
     * 控制台客户端
     *
     * @var ConsoleClient
     */
    private ConsoleClient $consoleClient;

    /**
     * 构造函数
     *
     * @param ConsoleClient $client
     */
    public function __construct(ConsoleClient $client)
    {
        $this->consoleClient = $client;
    }

    /**
     * @param string $email
     * @param string $password
     * @return mixed|void
     * @throws \Exception
     */
    public function login(string $email, string $password):array
    {
        return $this->consoleClient->post('/console/api/login', [
            'email' => $email,
            'password' => $password,
            'remember_me' => false,
        ]);
    }
}
