<template>
    <BaseLayer>
        <template v-slot:close>
            <a href="#" @click.prevent="$emit('close')">
                <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 28.2843L28.2843 3.09944e-05L30.4056 2.12135L2.12132 30.4056L0 28.2843Z" />
                    <path d="M2.12134 0L30.4056 28.2843L28.2843 30.4056L1.75834e-05 2.12132L2.12134 0Z" />
                </svg>
            </a>
        </template>
        <template v-slot:main>
            <div class="p-5">
                <div class="popup__content">
                    <div class="px-7">
                        <div class="row">
                            <div class="col-12">
                                <h2 class="margin-15 text-center">
                                    Добавить права
                                </h2>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div
                                    class="box__form"
                                    :class="{
                                        '-preloader': loading
                                    }"
                                >
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="box__checkbox text-left">
                                                <div class="wrapper-checkbox">
                                                    <label>
                                                        <input type="checkbox" :checked="isSelectedAllRoles" @click="onToggleAll">
                                                        <span>
                                                            <span class="box__checkbox-icon"></span>
                                                            <span class="box__checkbox-text">Полный доступ</span>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div
                                                v-for="role in userRolesList"
                                                :key="role.id"
                                                class="box__checkbox text-left"
                                            >
                                                <div class="wrapper-checkbox">
                                                    <label>
                                                        <input
                                                            v-model="selectedRoles[role.id]"
                                                            type="checkbox"
                                                        >
                                                        <span>
                                                            <span class="box__checkbox-icon"></span>
                                                            <span class="box__checkbox-text">{{ role.title }}</span>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        v-if="isSelectedAnyRole"
                                        class="row"
                                    >
                                        <div class="col-12">
                                            <BaseButton
                                                class="margin-15"
                                                @click="onSave"
                                            >
                                                Добавить
                                            </BaseButton>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </BaseLayer>
</template>

<script>
import { COMPANY_USER_ROLES, COMPANY_USER_ROLES_KEYS, COMPANY_USER_ROLES_BUYER, COMPANY_USER_ROLES_SUPPLIER } from '@/constants/company';
import { isArray, cloneDeep } from 'lodash';
import { fetchCompanyUserRole } from '@/api/company';

export default {
    name: 'UserAddRoleLayer',
    props: {
        userId: {
            type: [String, Number]
        },
        organization: {
            type: Object
        },
        shop: {
            type: Object
        }
    },
    fetch() {
        return this.fetchUserRoles();
    },
    data() {
        return {
            loading: true,
            activeRoles: [],
            userRolesDefault: cloneDeep(COMPANY_USER_ROLES),
            selectedRoles: cloneDeep(COMPANY_USER_ROLES_KEYS)
        };
    },
    computed: {
        userRolesList() {
            return Object.values(COMPANY_USER_ROLES).filter(role => {
                const canBeInList = ![COMPANY_USER_ROLES.ruleAll.id].includes(role.id);
                const hasAccess = this.$auth.user.isBuyer
                    ? COMPANY_USER_ROLES_BUYER.includes(role.id)
                    : COMPANY_USER_ROLES_SUPPLIER.includes(role.id);

                return hasAccess && canBeInList;
            });
        },
        isSelectedAllRoles() {
            return this.userRolesList.every(role => this.selectedRoles[role.id]);
        },
        isSelectedAnyRole() {
            return this.userRolesList.some(role => this.selectedRoles[role.id]);
        },
        submitData() {
            const rules = Object.keys(this.selectedRoles).map(roleKey => {
                if (this.selectedRoles[roleKey]) return roleKey;
            })
                .filter(Boolean);

            return {
                rules
            };
        }
    },
    beforeDestroy() {
        this.selectedRoles = COMPANY_USER_ROLES_KEYS;
    },
    methods: {
        async fetchUserRoles() {
            const data = await fetchCompanyUserRole(this.userId);

            if (data) {
                this.activeRoles = data.rules;
            }

            this.setActiveRoles();

            this.loading = false;
        },
        onToggleAll(e) {
            const isChecked = e.target.checked;
            const rolesKey = Object.values(COMPANY_USER_ROLES)
                .filter(role => this.$auth.user.isBuyer
                    ? COMPANY_USER_ROLES_BUYER.includes(role.id)
                    : COMPANY_USER_ROLES_SUPPLIER.includes(role.id))
                .map(x => x.id);

            rolesKey.forEach(roleKey => {
                this.selectedRoles[roleKey] = isChecked;
            });
        },
        setActiveRoles() {
            (isArray(this.activeRoles) ? this.activeRoles : []).forEach(activeRoleKey => {
                this.selectedRoles[activeRoleKey] = true;
            });
        },
        onSave() {
            this.$emit('close', this.submitData);
        }
    }
};
</script>
