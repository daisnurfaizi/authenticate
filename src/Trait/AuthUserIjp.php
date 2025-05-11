<?

namespace Ijp\Auth\Trait;

use Ramsey\Uuid\Guid\Guid;
use Tymon\JWTAuth\Contracts\JWTSubject;

trait AuthUserIjp
{

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Guid::uuid4();
            }
        });
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return string
     */
    public function getJWTIdentifier(): string
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array<string, string>
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'username' => $this->username,
        ];
    }
}
