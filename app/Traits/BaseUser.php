<?php namespace Fetch404\Core\Traits;

use Auth;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Thread;
use Fetch404\Core\Models\AccountConfirmation;
use Fetch404\Core\Models\User;
use Illuminate\Support\Facades\Storage;

trait BaseUser
{

    /**
     * Relationship functions
     * DO NOT MODIFY
     */

    /**
     * Get all the topics created by a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function topics()
    {
        return $this->hasMany('Fetch404\Core\Models\Topic');
    }

    /**
     * Get all the posts created by a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany('Fetch404\Core\Models\Post');
    }

    /**
     * Get all of the user's news posts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function news()
    {
        return $this->hasMany('Fetch404\Core\Models\News');
    }

    /**
     * Get any name changes the user has had.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function nameChanges()
    {
        return $this->hasMany('Fetch404\Core\Models\NameChange');
    }

    /**
     * Get any likes that the user gave.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likesGiven()
    {
        return $this->hasMany('Fetch404\Core\Models\Like', 'user_id');
    }

    /**
     * Get any likes that the user received.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likesReceived()
    {
        return $this->hasMany('Fetch404\Core\Models\Like', 'liked_user_id');
    }

    /**
     * Get a user's settings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function settings()
    {
        return $this->hasMany('Fetch404\Core\Models\UserSetting');
    }

    /**
     * Get the value of a certain setting for this user.
     * If it has not yet been set, a default value will be returned,
     * or if none is specified, null.
     *
     * @param $name
     * @param $default
     * @return string
     */
    public function getSetting($name, $default = null)
    {
        $setting = $this->settings()->where('name', '=', $name)->first();

        if ($setting == null) {
            return ($default == null ? null : $default);
        } else {
            return $setting->value;
        }
    }

    /**
     * Set a specific setting for this user.
     * If it has not yet been set, it will be created with the given value.
     *
     * @param $name
     * @param $value
     * @return void
     */
    public function setSetting($name, $value)
    {
        $setting = $this->settings()->where('name', '=', $name)->first();

        if ($setting == null) {
            return $this->settings()->create(array(
                'name'    => $name,
                'value'   => $value,
                'user_id' => $this->getId(),
            ));
        } else {
            return $setting->update(array(
                'value' => $value,
            ));
        }

        return false;
    }

    /**
     * Get all of the user's recent notifications.
     *
     * @return mixed
     */
    public function notifications()
    {
        return $this->hasMany('Fetch404\Core\Models\Notification')
            ->with(['user', 'subject'])
            ->take(5)
            ->latest();
    }

    /**
     * Get all of the user's unread notifications.
     *
     * @return mixed
     */
    public function unreadNotifications()
    {
        return $this->notifications()->unread($this->id);
    }

    /**
     * Get the user's account confirmation object.
     *
     * @return AccountConfirmation
     */
    public function getAccountConfirmation()
    {
        $confirmation = AccountConfirmation::where(
            'user_id',
            '=',
            $this->id
        )->first();

        if ($confirmation == null) {
            return null;
        }

        if ($this->isConfirmed()) {
            return null;
        }

        return $confirmation;
    }

    /**
     * Get a user's conversations.
     *
     * @return Thread
     */
    public function getConversations()
    {
        return Thread::forUser($this->id);
    }

    /**
     * Attribute functions
     *
     */
    /**
     * Is the user confirmed?
     *
     * @return boolean
     */
    public function isConfirmed()
    {
        return $this->confirmed == 1;
    }

    /**
     * Get the user's profile URL.
     *
     * @return string
     */
    public function getProfileURLAttribute()
    {
        return route('profile.get.show', ['slug' => $this->slug, 'id' => $this->id]);
    }

    /**
     * Get the user's name color.
     *
     * @return string
     */
    public function getRoleColorAttribute()
    {
        if ($this->hasRole('owner')) {
            return "#CC0000";
        } elseif ($this->hasRole('admin')) {
            return "#ccc";
        } else {
            return "#fff";
        }
    }

    /**
     * Get a user's prefix.
     *
     * @return string
     */
    public function getPrefixAttribute()
    {
        if ($this->roles->count() > 0) {
            $prefix = '[' . $this->roles()->orderBy('created_at', 'desc')->first()->display_name . ']';
            return $prefix;
        } else {
            return "[Member]";
        }
    }

    /**
     * Get the generated URL to a user's avatar.
     * Returns a link to the default avatar if the user does not have an avatar
     *
     * @param boolean $large
     * @return string
     */
    public function getAvatarURL($large = true)
    {
        $extensions = [
            'png',
            'jpg',
        ];

        foreach ($extensions as $ext) {
            if (Storage::exists('avatars/' . $this->id . '.' . $ext)) {
                return 'avatars/' . $this->id . '.' . $ext;
            }
        }

        return '/assets/img/defaultavatar' . ($large ? 'large' : '') . '.png';
    }
    /**
     * Other functions (role IDs, etc)
     *
     * Don't modify these!
     */

    /**
     * Returns user's current role ids only.
     * @return array|bool
     */
    public function currentRoleIds()
    {
        $roles   = $this->roles;
        $roleIds = false;
        if (!empty($roles)) {
            $roleIds = array();
            foreach ($roles as $role) {
                $roleIds[] = $role->id;
            }
        }
        return $roleIds;
    }

    /**
     * Save a user's roles, input is taken from the Select2 inputs.
     *
     * @param array $inputRoles
     * @return void
     */
    public function saveRoles($inputRoles)
    {
        if (!empty($inputRoles)) {
            $this->roles()->sync($inputRoles);
        } else {
            $this->roles()->detach();
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getJoinedOn()
    {
        $now = Carbon::now();
        $now->subDays(7);

        return ($this->created_at > $now->toDateTimeString() ? $this->created_at->diffForHumans() : $this->created_at->format('M jS, Y'));
    }

    public function getLastActivity()
    {
        $now = Carbon::now();
        $now->subDays(7);

        if ($this->last_active == null) {
            return ($this->created_at == null ? "never" : ($this->created_at > $now->toDateTimeString() ? $this->created_at->diffForHumans() : $this->created_at->format('M jS, Y')));
        }

        if (!Auth::check() && $this->getSetting("show_when_im_online", 1) == '0') {
            return "[hidden]";
        }

        if ($this->getSetting("show_when_im_online", 1) == '0' && Auth::id() != $this->id) {
            return "[hidden]";
        }

        return ($this->last_active > $now->toDateTimeString() ? $this->last_active->diffForHumans() : $this->last_active->format('M jS, Y'));
    }

    public function getLastActiveDesc()
    {
        if (!Auth::check() && $this->getSetting("show_when_im_online", 1) == '0') {
            return "[hidden]";
        }

        if ($this->getSetting("show_when_im_online", 1) == '0' && Auth::id() != $this->id) {
            return "[hidden]";
        }

        return $this->last_active_desc;
    }

    public function postCount()
    {
        return $this->posts()->count();
    }

    /**
     * Check to see if the current user is banned.
     *
     * @return bool
     */
    public function isBanned()
    {
        if ($this->banned_until != null) {
            return ($this->is_banned == 1 && $this->banned_until > Carbon::now()->toDateTimeString());
        }

        return $this->is_banned == 1;
    }

    /**
     * Check to see if the current user "is" a certain user
     * The only parameter type accepted is a User object.
     *
     * @param User $user
     * @return boolean
     */
    public function isUser(User $user)
    {
        if (is_null($user)) {
            return false;
        }

        return $this->getId() == $user->getId();
    }

    /**
     * Get a user's current "status" (their latest profile post)
     *
     * @return object
     */
    public function currentStatus()
    {
        $profilePost = $this->profilePosts()
            ->where('from_user_id', '=', $this->getId())
            ->where('to_user_id', '=', $this->getId())
            ->first();

        return $profilePost;
    }

    /**
     * Query scopes
     *
     * @param $query
     * @return mixed
     */
    public function scopeBanned($query)
    {
        return $query->where('is_banned', '=', 1);
    }
}
