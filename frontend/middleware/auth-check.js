import { roleForbiddenPages } from '@/constants/rolePages';
import { isUserPageGranted } from '@/helpers/user';

export default function({ route, redirect, $auth, error }) {
    const authPages = [
        'register',
        'login',
        'remind'
    ];

    if (!$auth.loggedIn && !authPages.includes(route.name)) {
        redirect('/login/');

        return;
    }

    if ($auth.loggedIn && authPages.includes(route.name)) {
        redirect('/company/');

        return;
    }

    let roleGrantedKey = '';
    if ($auth.user?.isSupplier) {
        roleGrantedKey = 'supplier';
    } else if ($auth.user?.isBuyer) {
        roleGrantedKey = 'buyer';
    } else if ($auth.user?.isModerator) {
        roleGrantedKey = 'moderator';
    }
    const roleGrantedForbiddenPages = roleForbiddenPages[roleGrantedKey] || [];

    if ([...roleGrantedForbiddenPages].includes(route.name)) {
        error({ statusCode: 403 });
    }

    if ($auth.user?.company && !$auth.user.company.visible && !['company-list', 'company'].includes(route.name)) {
        redirect({ name: 'company-list' });
    }

    if ($auth.user) {
        if (!isUserPageGranted($auth.user, route.name)) error({ statusCode: 403 });
    }
}
