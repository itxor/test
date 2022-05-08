<?php

declare(strict_types=1);

namespace App\Service\Email;

use App\Repository\EmailLogRepository;
use App\Repository\EmailRepository;
use App\Service\RabbitClient;
use App\Service\User\SendEmailDTO;
use Exception;
use Mailgun\Mailgun;
use PhpAmqpLib\Message\AMQPMessage;

class EmailService
{
    public const EMAIL_SEND_STATUS_SUCCESS = 1;
    public const EMAIL_SEND_STATUS_ERROR = 2;

    private EmailRepository $emailRepository;

    private EmailLogRepository $emailLogRepository;

    public function __construct(
        EmailRepository    $emailRepository,
        EmailLogRepository $emailLogRepository
    )
    {
        $this->emailRepository = $emailRepository;
        $this->emailLogRepository = $emailLogRepository;
    }

    public function getNotCheckedEmailsBatch(int $lastId, int $limit): array
    {
        return $this->emailRepository->findNotCheckedEmailsBatch($lastId, $limit);
    }

    public function isValidEmailByUserId(int $userId): bool
    {
        return $this->emailRepository->isValidEmailByUserId($userId);
    }

    /**
     * @throws Exception
     */
    public function dispatchEmailValidateMessage(ValidateDTO $dto): void
    {
        $connection = RabbitClient::get()->connect();
        $channel = $connection->channel();

        $channel->exchange_declare(
            RabbitClient::EMAIL_VALIDATE_EXCHANGE,
            'fanout',
            false,
            false,
            false
        );

        $msg = new AMQPMessage(serialize($dto));
        $channel->basic_publish($msg, RabbitClient::EMAIL_VALIDATE_EXCHANGE);

        $channel->close();
        $connection->close();
    }

    /**
     * Валидация email'ов у mailgun - платная, поэтому замокал её sleep'ом на 5 секунд.
     *
     * @throws Exception
     */
    public function validateEmail(ValidateDTO $dto): bool
    {
        echo sprintf("validate email: %s (user_id: %d)\n", $dto->getEmail(), $dto->getUserId());

        try {
//            $client = Mailgun::create(getenv('MAILGUN_KEY'));
//            $client->emailValidation()->validate($dto->getEmail());
            sleep(5);

            $this->emailRepository->updateCheckedStatus($dto->getEmailId(), true);
        } catch (Exception $exception) {
            $this->emailRepository->updateCheckedStatus($dto->getEmailId(), false);

            throw $exception;
        }

        return true;
    }

    /**
     * В методе заглушка на тестовые письма от mailgun.
     *
     * @throws Exception
     */
    public function sendEmail(SendEmailDTO $dto): void
    {
        try {
            echo sprintf("Отправка email на %s\n", $dto->getEmail());

            $client = Mailgun::create(getenv('MAILGUN_KEY'));
            $client->messages()->send(getenv('MAILGUN_DOMAIN'),
                [
                    'from' => 'Mailgun Sandbox <postmaster@sandbox7328ccefd6334c9aaa0da947fb14915d.mailgun.org>',
                    'to' => 'ivanov <ivanov.alex921@gmail.com>',
                    'subject' => 'Hello ivanov',
                    'text' => 'Congratulations ivanov, you just sent an email with Mailgun!  You are truly awesome! '
                ]
            );

            $this->addLog($dto, self::EMAIL_SEND_STATUS_SUCCESS);
        } catch (Exception $exception) {
            $this->addLog($dto, self::EMAIL_SEND_STATUS_ERROR);

            throw $exception;
        }
    }

    private function addLog(SendEmailDTO $dto, int $status): void
    {
        $this->emailLogRepository->addLog($dto->getUserId(), $dto->getEmailId(), $status);
    }
}
