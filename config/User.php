<?php

class User {
    // private
    private int $id;
    private string $email;
    private string $firstName;
    private string $lastName;
    private string $role;

    // Construct
    public function __construct(int $id, string $email, string $firstName, string $lastName, string $role) {
        $this->id = $id;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->role = $role;
    }

    // public methods (Getters)
    public function getId(): int {
        return $this->id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getFullName(): string {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getRole(): string {
        return $this->role;
    }
    
    // public method to check if is an admin
    public function isAdmin(): bool {
        return $this->role === 'admin';
    }
}
?>