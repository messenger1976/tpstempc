(function (window) {
    function isMale(gender) {
        var g = String(gender == null ? '' : gender).toUpperCase().replace(/^\s+|\s+$/g, '');
        if (!g) {
            return false;
        }
        return g.charAt(0) === 'M' && g.indexOf('FEMALE') !== 0;
    }

    function avatarBase() {
        return window.TAPSTEMCO_AVATAR_BASE || '';
    }

    function photoBase() {
        return window.TAPSTEMCO_PHOTO_BASE || '';
    }

    function defaultAvatar(gender) {
        return avatarBase() + (isMale(gender) ? 'male.svg' : 'female.svg');
    }

    function hasPhoto(photo) {
        var p = String(photo == null ? '' : photo).replace(/^\s+|\s+$/g, '');
        return p !== '' && p !== '0' && p.toLowerCase() !== 'avatar.gif';
    }

    window.memberDefaultAvatar = defaultAvatar;

    window.memberAccountStatus = function (userdata) {
        var labels = window.TAPSTEMCO_MEMBER_STATUS || {};
        var code = userdata && userdata.status !== undefined && userdata.status !== null ? String(userdata.status) : '';
        if (code === '1') {
            return { label: labels.active || 'Active', active: true };
        }
        if (code === '0') {
            return { label: labels.inactive || 'Inactive', active: false };
        }
        if (code === '2') {
            return { label: labels.deleted || 'Deleted', active: false };
        }
        return { label: code || (labels.inactive || 'Inactive'), active: false };
    };

    window.memberAvatarHtml = function (photo, gender, className) {
        className = className || 'cbu-member-photo';
        var fallback = defaultAvatar(gender);
        if (!hasPhoto(photo)) {
            return '<div class="' + className + ' avatar-fallback"><img src="' + fallback + '" alt=""/></div>';
        }
        return '<div class="' + className + '"><img src="' + photoBase() + String(photo) + '" alt="" onerror="this.onerror=null;this.src=\'' + fallback.replace(/'/g, '\\\'') + '\';"/></div>';
    };
})(window);
