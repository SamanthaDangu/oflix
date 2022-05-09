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
        /* eg avec une seule erreur
        Symfony\Component\Validator\ConstraintViolationList {#1034 ▼
        -violations: array:1 [▼
            0 => Symfony\Component\Validator\ConstraintViolation {#1051 ▼
            -message: "Your genre name must be at least 5 characters long"
            -messageTemplate: "Your genre name must be at least {{ limit }} characters long"
            -parameters: array:2 [▶]
            -plural: 5
            -root: App\Entity\Genre {#1019 ▶}
            -propertyPath: "name"
            -invalidValue: "az"
            -constraint: Symfony\Component\Validator\Constraints\Length {#1030 ▶}
            -code: "9ff3fdc4-b214-49db-8718-39c315e33d45"
            -cause: null
            }
        ]
        }
        */
        foreach ($errors as $error) {
            //dd($error);
            
            $this->message[] = "La valeur '" .$error->getInvalidValue(). "' ne respecte pas les règles de validation de la propriété '". $error->getPropertyPath() . "'";
        }
        // dd($this);
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