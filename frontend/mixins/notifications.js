import { fetchNotifications, fetchUnreadNotificationsCount, markNotificationAsRead } from '@/api/notification';
import { normalizeNotification, normalizeNotifications } from '@/normalizers/notification';
import jwt_decode from 'jwt-decode';
import { EventSourcePolyfill } from 'event-source-polyfill';
import alertMp3File from '~/static/alert.mp3';
const NOTIFICATION_EVENT_TYPES = [
    'newNotification'
];

export default {
    fetch() {
        return Promise.all([
            this.fetchNotifications(),
            this.fetchUnreadNotificationsCount()
        ]);
    },
    data() {
        return {
            alertAudioFile: alertMp3File,
            notifications: {
                unreadCount: 0,
                list: [],
                pagination: {
                    page: 1,
                    pages: 1
                },
                limit: 5,
                loadingMore: false
            },
            eventSourceParams: {
                url: '',
                instance: null,
                tokenDecoded: {
                    mercure: {
                        subscribe: []
                    }
                }
            }
        };
    },
    computed: {
        isPaginationEnabled() {
            return this.notifications.pagination.page < this.notifications.pagination.pages;
        },
        notificationList() {
            return (this.notifications && Array.isArray(this.notifications.list) ? this.notifications.list : []);
        },
        isNotificationsDisabled() {
            return !this.eventSubscriberToken;
        },
        eventSubscriberToken() {
            return this.$auth.user.eventSubscriberToken;
        },
        alertAudioEl() {
            return this.$refs['alertAudio'];
        }
    },
    mounted() {
        this.eventSourceParams.url = `${ location.protocol }//${ location.host }${ this.$nuxt.context.env.mercureSubscribeUrl }`;
        this.subscribeChat();
        window.addEventListener('beforeunload', () => {
            this.unsubscribeChat();
        });
    },
    beforeDestroy() {
        this.unsubscribeChat();
    },
    methods: {
        fetchNotifications({ isMore = false } = {}) {
            const page = this.notifications?.pagination?.page;
            const { limit } = this.notifications;

            return fetchNotifications({
                progress: false,
                params: {
                    page,
                    limit
                }
            })
                .then(response => {
                    if (isMore) {
                        this.notifications.list = [...this.notifications.list, ...normalizeNotifications(response.items)];
                    } else {
                        this.notifications.list = normalizeNotifications(response.items);
                    }

                    this.notifications.pagination = response.pagination;
                });
        },
        fetchUnreadNotificationsCount() {
            return fetchUnreadNotificationsCount({
                progress: false
            })
                .then(response => {
                    this.notifications.unreadCount = response?.count || 0 ? Number(response.count) : 0;
                });
        },
        async onNotificationLoadMore() {
            if (!this.isPaginationEnabled) return;

            this.notifications.pagination.page += 1;
            this.notifications.loadingMore = true;

            try {
                await this.fetchNotifications({ isMore: true });
            } catch (e) {
                console.log(e);
            }

            this.notifications.loadingMore = false;
        },
        async markNotificationAsRead(id) {
            console.log('markNotificationAsRead:id', id);
            try {
                await markNotificationAsRead({ lastNotificationId: id });
                await this.fetchUnreadNotificationsCount();
            } catch (e) {
                console.log(e);
            }
        },
        subscribeChat() {
            if (this.isNotificationsDisabled) return;

            const esToken = this.eventSubscriberToken;
            const parsedEsToken = jwt_decode(esToken);
            const url = new URL(this.eventSourceParams.url);
            this.eventSourceParams.tokenDecoded = parsedEsToken;

            if (parsedEsToken?.mercure?.subscribe && Array.isArray(parsedEsToken.mercure.subscribe)) {
                parsedEsToken.mercure.subscribe.forEach((el) => {
                    url.searchParams.append('topic', el);
                });
            }

            this.eventSourceParams.instance = new EventSourcePolyfill(url, {
                headers: {
                    Authorization: 'Bearer ' + esToken
                },
                lastEventIdQueryParameterName: 'Last-Event-Id'
            });

            this.eventSourceParams.instance.onmessage = event => {
                const message = JSON.parse(event.data);
                if (!message) return;
                const messageType = message['@eventMessageType'];

                if (NOTIFICATION_EVENT_TYPES.includes(messageType)) {
                    this.fetchUnreadNotificationsCount();
                    this.notifications.list.unshift(normalizeNotification(message));
                }
            };
        },
        unsubscribeChat() {
            if (this.eventSourceParams.instance) {
                this.eventSourceParams.instance.close();
                this.eventSourceParams.instance = null;
            }
        },
        alertSound() {
            this.alertAudioEl && this.alertAudioEl.play();
        }
    }
};
