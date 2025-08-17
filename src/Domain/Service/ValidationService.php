<?php

namespace App\Domain\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Domain\Exception\ValidationException;

class ValidationService
{
    public function __construct(private ValidatorInterface $validator) {}

    public function validate(object $entity): void
    {
        $violations = $this->validator->validate($entity);
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new ValidationException('Dados inválidos', $errors);
        }
    }
}