<?php

namespace App\Command;

use App\Repository\EmailRepository;
use App\Service\Email\EmailService;
use App\Service\Email\ValidateDTO;

class CheckUsersEmailsCommand implements CommandInterface
{
    public function execute(): void
    {
        $emailService = new EmailService(new EmailRepository());

        $emails = $emailService->getNotCheckedEmails();
        if (0 === count($emails)) {
            return;
        }

        foreach ($emails as $email) {
            $dto = new ValidateDTO($email['id'], $email['email']);
            $emailService->dispatchEmailValidateMessage($dto);
        }
    }
}
