<template>
    <div class="box__app__header">
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <Breadcrumbs />
                </div>
                <div class="col-6">
                    <div class="navbar__item">
                        <div class="navbar__notification-icon">
                            <a
                                href="#"
                                :style="{ backgroundImage: `url(${ require('@/assets/img/icon/header-bell.svg') })` }"
                                @click.prevent
                            >
                                <span v-if="notifications.unreadCount">{{ notifications.unreadCount }}</span>
                            </a>

                            <div
                                v-if="notificationList.length"
                                ref="notificationList"
                                class="navbar__notification-list"
                            >
                                <ClientOnly>
                                    <PerfectScrollbar
                                        :options="{
                                            wheelSpeed: 0.8,
                                            wheelPropagation: true,
                                            minScrollbarLength: 20
                                        }"
                                        tag="ul"
                                        role="main"
                                    >
                                        <li
                                            v-for="notification in notificationList"
                                            :key="notification.id"
                                            ref="notificationItem"
                                        >
                                            <span class="notification__item-head">
                                                <span class="notification__date">
                                                    {{ notification.createdAt }}
                                                </span>
                                                <span class="notification__company"></span>
                                            </span>
                                            <span class="notification-name">
                                                <NuxtLink
                                                    class="notification__event"
                                                    :to="{ path: notification.path }"
                                                    @click.native="markNotificationAsRead(notification.id)"
                                                >
                                                    {{ notification.title }}
                                                </NuxtLink>
                                            </span>
                                        </li>
                                        <li
                                            v-if="isPaginationEnabled"
                                            class="navbar__notification-more"
                                        >
                                            <a
                                                href="#"
                                                :class="{
                                                    '-preloader': notifications.loadingMore
                                                }"
                                                @click.prevent="onNotificationLoadMore"
                                            >
                                                Посмотреть все уведомления
                                            </a>
                                        </li>
                                    </PerfectScrollbar>
                                </ClientOnly>
                            </div>
                        </div>
                        <div class="navbar__profile">
                            <div
                                v-if="profile.file"
                                class="wrapper__profile-icon"
                            >
                                <span :style="{ backgroundImage: `url(${ profile.file })` }"></span>
                            </div>
                            <div class="wrapper__profile-name">
                                {{ profile.title }}
                            </div>
                            <div class="wrapper__profile-list">
                                <ul>
                                    <li>
                                        <NuxtLink :to="{ name: 'profile' }">
                                            Профиль
                                        </NuxtLink>
                                    </li>
                                    <li v-if="isModerating">
                                        <a href="#" @click.prevent="goToModeratorCabinet">
                                            Вернуться в личный кабинет модератора
                                        </a>
                                    </li>
                                    <li v-if="!$auth.user.isModerator">
                                        <NuxtLink :to="{ name: 'company-list' }">
                                            Мои организации
                                        </NuxtLink>
                                    </li>
                                    <li>
                                        <a href="#" @click.prevent="onLogout">Выход</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <audio v-show="false" ref="alertAudio" :src="alertAudioFile"></audio>
    </div>
</template>
<script>
import notificationsMixin from '@/mixins/notifications';
import { mapState } from 'vuex';

export default {
    name: 'TheHeader',
    mixins: [
        notificationsMixin
    ],
    data() {
        return {};
    },
    computed: {
        ...mapState('page', ['isModerating']),
        profile() {
            return {
                title: this.$auth.user?.firstName || this.$auth.user?.company?.title,
                file: this.$auth.user?.company?.file?.url
            };
        }
    },
    methods: {
        async onLogout() {
            try {
                await this.$auth.logout();
            } catch (e) {
                console.log('Unable to log out', e);
            }

            this.$router.push({ name: 'login' });
        },
        goToModeratorCabinet() {
            const data = this.$cookies.get('authData');

            this.$auth.setUser(data.user);
            this.$auth.setUserToken(data.token);
            this.$router.push({ name: 'requests' });

            this.$cookies.remove('authData');
            this.$store.dispatch('page/setModerating', false);
        }
    }
};
</script>
