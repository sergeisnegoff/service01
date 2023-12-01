<template>
    <header
        id="leftNavigation"
        :class="{
            'header-toggle': navigationOpened || isMinimal
        }"
    >
        <div class="wrapper__header">
            <div class="box__logo">
                <NuxtLink
                    :class="$route.name === 'company-list' && 'pointer-events-none'"
                    to="/"
                >
<!--                    <img :src="require('@/assets/img/icon/logo.svg')" alt="">-->
                </NuxtLink>
            </div>
            <div v-if="!isMinimal" class="btn__nav">
                <!-- active class for nav-button - .btn__nav-active-->
                <button @click="toggleNavigation">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
        <div class="wrapper__nav">
            <div v-if="!isMinimal" class="box__nav">
                <nav v-if="getMenu && getMenu.length">
                    <ul>
                        <li
                            v-for="item in getMenu"
                            :key="item.id"
                            :class="{
                                'box__nav-active': item.link && item.link.name === $route.name
                            }"
                        >
                            <NuxtLink v-if="item.link" :target="typeof(item.link) === 'string' ? '_blank' : '_self'" :to="item.link">
                                <span v-if="item.icon" :style="{ backgroundImage: `url(${ item.icon })` }"></span>{{ item.title }}
                            </NuxtLink>
                            <a v-else href="#" @click.prevent>
                                <span v-if="item.icon" :style="{ backgroundImage: `url(${ item.icon })` }"></span>{{ item.title }}
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="box__nav-bottom">
                <div class="box__nav__contactdata">
                    <ul>
                        <li>Тех. поддержка</li>
                        <li v-if="contacts.phone">
                            <a :href="`tel:${ contacts.phone }`">{{ contacts.phone }}</a>
                        </li>
                        <li v-if="contacts.email">
                            <a :href="`email:${ contacts.email }`">{{ contacts.email }}</a>
                        </li>
                    </ul>
                </div>
                <div class="btn-white">
                    <a href="#" @click.prevent="$layer.open('FeedbackLayer')">
                        <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/nav-alert.svg') })` }"></span>
                        Сообщить о проблеме
                    </a>
                </div>
            </div>
        </div>
    </header>
</template>
<script>
import { isUserPageGranted } from '@/helpers/user';

export default {
    name: 'Navigation',
    storageItems: [
        'menuContacts',
        'systemApiCommon'
    ],
    props: {
        mode: {
            type: String,
            default: ''
        }
    },
    data: () => ({
        navigationOpened: false
    }),
    computed: {
        contacts() {
            return this.menuContacts || {};
        },
        api() {
            return this.menuApi || {};
        },
        isMinimal() {
            return this.mode === 'empty';
        },
        getSupplierMenu() {
            return [
                {
                    id: 1,
                    title: 'Накладные',
                    link: { name: 'invoice' },
                    icon: require('@/assets/img/icon/nav-2.svg')
                },
                {
                    id: 2,
                    title: 'Номенклатура',
                    link: {
                        name: 'products'
                    },
                    icon: require('@/assets/img/icon/nav-1.svg')
                },
                {
                    id: 3,
                    title: 'Покупатели',
                    link: { name: 'buyer' },
                    icon: require('@/assets/img/icon/nav-3.svg')
                },
                {
                    id: 4,
                    title: 'Моя организация',
                    link: { name: 'company' },
                    icon: require('@/assets/img/icon/nav-4.svg')
                },
                {
                    id: 6,
                    title: this.systemApiCommon?.title,
                    link: { name: 'company-system-api' },
                    icon: require('@/assets/img/icon/nav-6.svg')
                }
            ].filter(item => isUserPageGranted(this.$auth.user, item.link?.name));
        },
        getBuyerMenu() {
            return [
                {
                    id: 1,
                    title: 'Накладные',
                    link: { name: 'invoice' },
                    icon: require('@/assets/img/icon/nav-2.svg')
                },
                {
                    id: 3,
                    title: 'Поставщики',
                    link: { name: 'supplier' },
                    icon: require('@/assets/img/icon/nav-3.svg')
                },
                {
                    id: 4,
                    title: 'Меркурий',
                    link: { name: 'mercury' },
                    icon: require('@/assets/img/icon/nav-11.svg')
                },
                {
                    id: 5,
                    title: 'Моя организация',
                    link: { name: 'company' },
                    icon: require('@/assets/img/icon/nav-4.svg')
                }
                // {
                //     id: 6,
                //     title: 'Ценники',
                //     link: null, // ToDo: Добавить ссылку { name: '' }
                //     icon: require('@/assets/img/icon/nav-13.svg')
                // },
                // {
                //     id: 7,
                //     title: 'Импорт',
                //     link: null, // ToDo: Добавить ссылку { name: '' }
                //     icon: require('@/assets/img/icon/nav-5.svg')
                // },
                // {
                //     id: 8,
                //     title: 'Экспорт',
                //     link: null, // ToDo: Добавить ссылку { name: '' }
                //     icon: require('@/assets/img/icon/nav-5-1.svg')
                // }
            ].filter(item => isUserPageGranted(this.$auth.user, item.link?.name));
        },
        getModeratorMenu() {
            return [
                {
                    id: 1,
                    title: 'Заявки',
                    link: {
                        name: 'requests'
                    },
                    icon: require('@/assets/img/icon/nav-4.svg')
                },
                {
                    id: 2,
                    title: 'Поставщики',
                    link: { name: 'supplier' },
                    icon: require('@/assets/img/icon/nav-3.svg')
                },
                {
                    id: 3,
                    title: 'Покупатели',
                    link: { name: 'buyer' },
                    icon: require('@/assets/img/icon/nav-3.svg')
                }
            ];
        },
        getMenu() {
            const isSupplier = this.$auth.user?.isSupplier;
            const isBuyer = this.$auth.user?.isBuyer;
            const isModerator = this.$auth.user?.isModerator;

            if (isSupplier) {
                return this.getSupplierMenu;
            }

            if (isBuyer) {
                return this.getBuyerMenu;
            }

            if (isModerator) {
                return this.getModeratorMenu;
            }

            return [];
        }
    },
    created() {
        if (process.browser) {
            document.removeEventListener('click', this.onClick);
            document.addEventListener('click', this.onClick);
        }
    },
    destroyed() {
        if (process.browser) {
            document.removeEventListener('click', this.onClick);
        }
    },
    methods: {
        toggleNavigation() {
            this.navigationOpened = !this.navigationOpened;
        },
        onClick(e) {
            if (!e.target.closest('#leftNavigation') && this.navigationOpened) {
                this.navigationOpened = false;
            }
        }
    }
};
</script>
