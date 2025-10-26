<?php

namespace App\Events;

class NewMessage
{
    public function __construct(
        public string $body,
        public string $name,
        public string $email,
        public string $subject,
        public string $createdAt
    ) {
    }
}
