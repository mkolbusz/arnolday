<?php

namespace AppBundle\Services;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use AppBundle\Entity\Task;

class NotificationMailer {

    private $securityTokenStorage;
    private $appName;
    private $mailerFrom;
    private $mailer;
    private $message;
    private $twig;

    function __construct(\Swift_Mailer $mailer, TokenStorage $securityTokenStorage, TwigEngine $twig, $appName, $mailerFrom) {
        $this->mailer = $mailer;
        $this->securityTokenStorage = $securityTokenStorage;
        $this->appName = $appName;
        $this->mailerFrom = $mailerFrom;
        $this->twig = $twig;
    }

    public function createMessage(Task $task) {
        $user = $this->securityTokenStorage->getToken()->getUser();
        $assignee = $task->getAssignee();
        $this->message = $this->mailer->createMessage()
            ->setSubject('Notification: New Task')
            ->setFrom(array($this->mailerFrom => $this->appName))
            ->setTo($assignee->getEmail())
            ->setBody(
                $this->twig->render(
                    'emails/new_task.html.twig', array(
                        'task' => $task,
                        'user' => $user,
                        'assignee' => $assignee)
                ), 'text/html');
    }

    public function send() {
        $this->mailer->send($this->message);
    }
}