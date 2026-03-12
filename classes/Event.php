<?php

class Event {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /**
     * Returns all events ordered by date ascending.
     */
    public function getAll(): array {
        $stmt = $this->conn->prepare(
            'SELECT * FROM events ORDER BY event_date ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Returns a single event by ID, or false if not found.
     */
    public function findById(string $id): array|false {
        $stmt = $this->conn->prepare(
            'SELECT * FROM events WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Aggregate query: returns ticket sales summary per event.
     * Uses COUNT and SUM to satisfy the aggregate requirement.
     */
    public function getSalesSummary(): array {
        $stmt = $this->conn->prepare(
            'SELECT e.id, e.title, e.category,
                    COUNT(t.id)        AS total_orders,
                    SUM(t.quantity)    AS total_tickets_sold,
                    SUM(t.total_price) AS total_revenue
             FROM events e
             LEFT JOIN tickets t ON t.event_id = e.id
             GROUP BY e.id, e.title, e.category
             ORDER BY total_revenue DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
