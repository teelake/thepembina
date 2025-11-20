<?php
/**
 * Newsletter Subscriber Model
 */

namespace App\Models;

use App\Core\Model;

class NewsletterSubscriber extends Model
{
    protected $table = 'newsletter_subscribers';

    public function createSubscriber($data)
    {
        // Avoid duplicates
        $existing = $this->findByEmail($data['email']);
        if ($existing) {
            return $existing['id'];
        }
        return $this->create($data);
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
}

<?php
/**
 * Newsletter Subscriber Model
 */

namespace App\Models;

use App\Core\Model;

class NewsletterSubscriber extends Model
{
    protected $table = 'newsletter_subscribers';

    public function subscribe($email, $name = null)
    {
        $existing = $this->findByEmail($email);
        if ($existing) {
            if ($existing['status'] === 'active') {
                return true;
            }
            return $this->update($existing['id'], ['status' => 'active', 'name' => $name]);
        }
        return $this->create([
            'email' => $email,
            'name' => $name,
            'status' => 'active'
        ]);
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
}


