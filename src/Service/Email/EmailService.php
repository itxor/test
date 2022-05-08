<?php

namespace App\Service\Email;

use App\Repository\EmailLogRepository;
use App\Repository\EmailRepository;
use App\Service\RabbitClient;
use App\Service\User\SendEmailDTO;
use Exception;
use PhpAmqpLib\Message\AMQPMessage;

class EmailService
{
    public const EMAIL_SEND_STATUS_SUCCESS = 1;
    public const EMAIL_SEND_STATUS_ERROR = 2;

    private EmailRepository $emailRepository;

    private EmailLogRepository $emailLogRepository;

    public function __construct(
        EmailRepository $emailRepository,
        EmailLogRepository $emailLogRepository
    )
    {
        $this->emailRepository = $emailRepository;
        $this->emailLogRepository = $emailLogRepository;
    }

    public function getNotCheckedEmailsBatch(int $lastId, int $limit) : array
    {
        return $this->emailRepository->findNotCheckedEmailsBatch($lastId, $limit);
    }

    public function isValidEmailByUserId(int $userId) : bool
    {
        return $this->emailRepository->isValidEmailByUserId($userId);
    }

    /**
     * @throws Exception
     */
    public function dispatchEmailValidateMessage(ValidateDTO $dto) : void
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

    public function validateEmail(ValidateDTO $dto) : bool
    {
        sleep(5);

        $this->emailRepository->updateCheckedStatus($dto->getEmailId(), true);

        return true;
    }

    /**
     * @throws Exception
     */
    public function sendEmail(SendEmailDTO $dto) : void
    {
        try {
            // some works
            sleep(5);

            $this->addLog($dto, self::EMAIL_SEND_STATUS_SUCCESS);
        } catch (Exception $exception) {
            $this->addLog($dto, self::EMAIL_SEND_STATUS_ERROR);

            throw $exception;
        }
    }

    private function addLog(SendEmailDTO $dto, int $status) : void
    {
        $this->emailLogRepository->addLog($dto->getUserId(), $dto->getEmailId(), $status);
    }
}
