<?php
/**
 * Event Model
 */

namespace App\Models;

use App\Core\Model;

class Event extends Model
{
    protected $table = 'events';

    public function getUpcoming($limit = 3)
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'upcoming' ORDER BY event_date ASC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

<?php
/**
 * Event Model
 */

namespace App\Models;

use App\Core\Model;

class Event extends Model
{
    protected $table = 'events';

    public function getUpcoming($limit = 3)
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'upcoming' AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}


