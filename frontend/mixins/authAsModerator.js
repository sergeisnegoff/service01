import { userAuthorizeAsModerator } from '@/api/user';

export default {
    data: () => ({
        authorizing: false
    }),
    methods: {
        async authAsModerator(companyId) {
            this.authorizing = true;

            try {
                const resp = await userAuthorizeAsModerator({ companyId });

                this.$cookies.set('authData', {
                    user: this.$auth.user,
                    token: this.$auth.strategy.token.get()
                });

                this.$auth.setUser(resp.user);
                this.$auth.setUserToken(resp.token.token);
                this.$store.dispatch('page/setModerating', true);
                this.$router.push({ name: 'company' });
            } catch (e) {
                console.log(e);
                this.$layer.alert({
                    message: 'Произошла ошибка'
                });
            }

            this.authorizing = false;
        }
    }
};
