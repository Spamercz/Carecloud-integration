<?php

namespace App\Model\BigCommerce;

use DateTime;
use Exception;
use InvalidArgumentException;

final class BigCommerceUserFactory
{

    public function create(mixed $data): BigCommerceUser
    {
        try {
            // Error handler can catch missing properties
            set_error_handler([$this, 'errorHandler'], E_NOTICE);

            $user = new BigCommerceUser(
                $data->id,
                $data->firstName,
                $data->lastName,
                $data->email,
                new DateTime($data->dateOfBirth),
                $data->emailVerified,
                new DateTime($data->signUpDate),
            );

            restore_error_handler();

            return $user;

        } catch (Exception $exception) {
            throw new InvalidArgumentException(
                $exception->getMessage(),
                previous: $exception
            );
        }
    }

     protected function errorHandler(
        int     $errno,
        string  $errorString,
        ?string $errorFile,
        ?int    $errorLine,
        ?array  $errcontext
    ): bool
    {
        if (str_contains($errorString, 'Undefined property')) {
            if ($errorFile) {
                $errorString .= sprintf("; File: '%s'.", $errorFile);
            }
            if ($errorLine) {
                $errorString .= sprintf("; Line: '%d'.", $errorLine);
            }

            throw new InvalidArgumentException($errorString);
        }

        return FALSE;
    }

}
