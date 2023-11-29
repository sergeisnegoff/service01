<?php

declare(strict_types=1);

namespace App\Command;

use Creonit\MailingBundle\Mailing;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;

class TestMailCommand extends Command
{
    private Mailing $mailing;
    private MailerInterface $mailer;

    public function __construct(string $name = null, Mailing $mailing, MailerInterface $mailer)
    {
        parent::__construct($name);
        $this->mailing = $mailing;
        $this->mailer = $mailer;
    }

    protected function configure()
     {
         $this->setName('test:mail');
     }

     protected function execute(InputInterface $input, OutputInterface $output)
     {
         $message = $this->mailing->buildMessage('invite_company_user', [
             'password' => 'Пароль',
             'link' => 'Ссылка',
         ]);

         $message->addTo('s.nikolenkov@creonit.ru');
         $this->mailer->send($message);
     }
}
