<?php

namespace App\Models;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class JsonError
{

    private $error;
    private $message;

    public function __construct(int $error = Response::HTTP_NOT_FOUND, string $message = "Not Found")
    {
        $this->error = $error;
        $this->message[] = $message;
    }

    public function setValidationErrors(ConstraintViolationListInterface $errors)
    {
        foreach ($errors as $error) {
            $this->message[] = "La valeur '" . $error->getInvalidValue() . "' ne respecte pas les règles de validation de la propriété '" . $error->getPropertyPath() . "'";
        }
    }

    /**
     * Get the value of error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set the value of error
     *
     * @return  self
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get the value of message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set the value of message
     *
     * @return  self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}
