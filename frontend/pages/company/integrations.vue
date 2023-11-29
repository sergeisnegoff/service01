<template>
    <section class="box__companyuser__page">
        <div class="container">
            <div class="row">
                <div class="col-9">
                    <h1>Моя организация</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <CompanyNavigation />
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="box__tabs-type">
                        <ul>
                            <li
                                v-for="item in integrationsSections"
                                :key="item.id"
                                :class="formData.integration === item.id && 'active pointer-events-none'"
                            >
                                <a
                                    href="#"
                                    @click.prevent="formData.integration = item.id"
                                >
                                    {{ item.title }}
                                </a>
                            </li>
                        </ul>
                        <ul v-if="formData.integration === 'electronic-management'">
                            <li
                                v-for="item in currentIntegrationSection.children"
                                :key="item.id"
                                :class="formData.electronicManagement === item.id && 'active pointer-events-none'"
                            >
                                <a
                                    href="#"
                                    @click.prevent="formData.electronicManagement = item.id"
                                >
                                    {{ item.title }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="box__tabs-item">
                        <div class="box__integration">
                            <div
                                v-if="formData.integration === 'accounting-system'"
                                class="row"
                            >
                                <div class="col-6">
                                    <div class="box__form margin-15">
                                        <form>
                                            <div class="row">
                                                <div class="col-8">
                                                    <SelectField
                                                        v-model="formData.accountingSystem"
                                                        :options="formInfo.accountingSystem.options"
                                                        track-by="id"
                                                        placeholder="Выбрать учетную систему"
                                                        label="title"
                                                    />
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="box__form box__integration">
                                        <template v-if="formData.integration === 'mercury'">
                                            <div v-if="accessToken" class="mb-4">
                                                <span>Ваш токен доступа:</span>
                                                <span class="code-wrap">{{ accessToken }}</span>
                                            </div>
                                            <form
                                                v-if="$auth.user.isSupplier"
                                                class="mb-4"
                                                :class="{
                                                    '-preloader': pending.mercury
                                                }"
                                                @submit.prevent="onSendMercury"
                                            >
                                                <div class="row">
                                                    <div class="col-5">
                                                        <h4>Настройки Меркурий</h4>
                                                    </div>
                                                </div>
                                                <ListController
                                                    ref="listController"
                                                    v-model="doctors"
                                                    :fetch-api="fetchDoctors"
                                                    :fetch-options="fetchDoctorsOptions"
                                                    :show-text-if-empty="false"
                                                >
                                                    <template v-slot="{ items }">
                                                        <BaseTable
                                                            :headers="doctorsTable.headers"
                                                            :items="doctorsTable.items"
                                                        >
                                                            <template #edit="{ index, headers }">
                                                                <div :class="'col-' + headers[0].col">
                                                                    <InputField
                                                                        v-model="items[index].externalCode"
                                                                        :error="doctorsErrors.doctors && doctorsErrors.doctors[index] && doctorsErrors.doctors[index].externalCode"
                                                                        :placeholder="headers[0].title"
                                                                    />
                                                                </div>
                                                                <div :class="'col-' + headers[1].col">
                                                                    <InputField
                                                                        v-model="items[index].veterinaryEmail"
                                                                        :error="doctorsErrors.doctors && doctorsErrors.doctors[index] && doctorsErrors.doctors[index].veterinaryEmail"
                                                                        :placeholder="headers[0].title"
                                                                    />
                                                                </div>
                                                                <div class="col-2 flex justify-end">
                                                                    <div class="wraapper__button-nowrap">
                                                                        <div class="btn btn__icon-purple">
                                                                            <button @click.prevent="changeMercuryDoctor(items[index], index)">
                                                                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="btn-whitepurple btn__icon-whitepurple">
                                                                            <button @click.prevent="deleteMercuryDoctor(items[index])">
                                                                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })` }"></span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                            <template #actions="{ index }">
                                                                <div class="col-2">
                                                                    <div class="wraapper__button-nowrap flex justify-end">
                                                                        <div class="btn btn__icon-purple">
                                                                            <button @click.prevent="items[index].isEdit = true">
                                                                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="btn-whitepurple btn__icon-whitepurple">
                                                                            <button @click.prevent="deleteMercuryDoctor(items[index])">
                                                                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })` }"></span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </BaseTable>
                                                    </template>
                                                </ListController>
                                                <BaseTable :items="[{ isEdit: true }]">
                                                    <template #edit>
                                                        <div class="col-5">
                                                            <InputField
                                                                v-model="formDataDoctors.externalCode"
                                                                :error="formDataErrors.externalCode"
                                                                placeholder="ID хозяйствующего субъекта"
                                                            />
                                                        </div>
                                                        <div class="col-5">
                                                            <InputField
                                                                v-model="formDataDoctors.veterinaryEmail"
                                                                :error="formDataErrors.veterinaryEmail"
                                                                placeholder="Email"
                                                            />
                                                        </div>
                                                        <div class="col-2 flex justify-end">
                                                            <div class="btn btn__icon-purple">
                                                                <button @click.prevent="addMercuryDoctor">
                                                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </BaseTable>
                                            </form>
                                            <form
                                                v-if="$auth.user.isBuyer"
                                                class="mb-4"
                                                :class="{
                                                    '-preloader': pending.mercury
                                                }"
                                                @submit.prevent="onSendMercury"
                                            >
                                                <div class="row">
                                                    <div class="col-5">
                                                        <h4>Настройки Меркурий</h4>
                                                        <p>
                                                            Данные реквизиты предоставляются по отдельной заявке. С пошаговой инструкцией по предоставлению доступа к интеграционному шлюзу ветис.api  можете ознакомиться в документации:
                                                            <a
                                                                href="http://help.vetrf.ru/wiki/Ветис.API#.D0.9F.D1.80.D0.B5.D0.B4.D0.BE.D1.81.D1.82.D0.B0.D0.B2.D0.BB.D0.B5.D0.BD.D0.B8.D0.B5_.D0.B4.D0.BE.D1.81.D1.82.D1.83.D0.BF.D0.B0"
                                                                target="_blank"
                                                            >
                                                                http://help.vetrf.ru/wiki/Ветис.API#.D0.9F.D1.80.D0.B5.D0.B4.D0.BE.D1.81.D1.82.D0.B0.D0.B2.D0.BB.D0.B5.D0.BD.D0.B8.D0.B5_.D0.B4.D0.BE.D1.81.D1.82.D1.83.D0.BF.D0.B0
                                                            </a>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-5">
                                                        <div class="box__input">
                                                            <label>Идентификатор заявки в клиентской системе</label>
                                                            <input
                                                                v-model="settings.mercury.issuerId"
                                                                :class="{
                                                                    'is-invalid': errors.mercury.issuerId
                                                                }"
                                                                type="text"
                                                                placeholder="Идентификатор заявки в клиентской системе"
                                                            >
                                                            <label v-if="errors.mercury.issuerId" class="invalid-feedback">{{ errors.mercury.issuerId }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-5">
                                                        <div class="box__input">
                                                            <label>Логин ветеринарного врача</label>
                                                            <input
                                                                v-model="settings.mercury.veterinaryLogin"
                                                                :class="{
                                                                    'is-invalid': errors.mercury.veterinaryLogin
                                                                }"
                                                                type="text"
                                                                placeholder="Логин ветеринарного врача"
                                                            >
                                                            <label v-if="errors.mercury.veterinaryLogin" class="invalid-feedback">{{ errors.mercury.veterinaryLogin }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-5">
                                                        <div class="box__input">
                                                            <label>Логин</label>
                                                            <input
                                                                v-model="settings.mercury.login"
                                                                type="text"
                                                                placeholder="Логин для авторизации в security-слое"
                                                                :class="{
                                                                    'is-invalid': errors.mercury.login
                                                                }"
                                                            >
                                                            <label v-if="errors.mercury.login" class="invalid-feedback">{{ errors.mercury.login }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-5">
                                                        <div class="box__input">
                                                            <label>Пароль</label>
                                                            <input
                                                                v-model="settings.mercury.password"
                                                                :class="{
                                                                    'is-invalid': errors.mercury.password
                                                                }"
                                                                type="text"
                                                                placeholder="Пароль для авторизации в security-слое"
                                                            >
                                                            <label v-if="errors.mercury.password" class="invalid-feedback">{{ errors.mercury.password }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-5">
                                                        <div class="box__input">
                                                            <label>APIKey</label>
                                                            <input
                                                                v-model="settings.mercury.apiKey"
                                                                :class="{
                                                                    'is-invalid': errors.mercury.apiKey
                                                                }"
                                                                type="text"
                                                                placeholder="APIKey"
                                                            >
                                                            <label v-if="errors.mercury.apiKey" class="invalid-feedback">{{ errors.mercury.apiKey }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-5">
                                                        <BaseButton
                                                            type="submit"
                                                            :disabled="pending.mercury"
                                                        >
                                                            Сохранить
                                                        </BaseButton>
                                                    </div>
                                                </div>
                                            </form>
                                        </template>
                                        <form
                                            v-if="formData.integration === 'accounting-system' && formData.accountingSystem && formData.accountingSystem.id === 'iiko'"
                                            class="mb-4"
                                            :class="{
                                                '-preloader': pending.iiko
                                            }"
                                            @submit.prevent="onSendIiko"
                                        >
                                            <div class="row">
                                                <div class="col-5">
                                                    <h4>Настройки iiko</h4>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Логин</label>
                                                        <input
                                                            v-model="settings.iiko.login"
                                                            type="text"
                                                            placeholder="Логин"
                                                            :class="{
                                                                'is-invalid': errors.iiko.login
                                                            }"
                                                        >
                                                        <label v-if="errors.iiko.login" class="invalid-feedback">{{ errors.iiko.login }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Пароль</label>
                                                        <input
                                                            v-model="settings.iiko.password"
                                                            :class="{
                                                                'is-invalid': errors.iiko.password
                                                            }"
                                                            type="text"
                                                            placeholder="Пароль"
                                                        >
                                                        <label v-if="errors.iiko.password" class="invalid-feedback">
                                                            {{ errors.iiko.password }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Адрес сервера</label>
                                                        <input
                                                            v-model="settings.iiko.url"
                                                            :class="{
                                                                'is-invalid': errors.iiko.url
                                                            }"
                                                            type="text"
                                                            placeholder="Адрес сервера"
                                                        >
                                                        <label v-if="errors.iiko.url" class="invalid-feedback">{{ errors.iiko.url }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex">
                                                <BaseButton
                                                    class="mr-2"
                                                    type="submit"
                                                    :disabled="pending.iiko"
                                                >
                                                    Сохранить
                                                </BaseButton>
                                                <BaseButton
                                                    :disabled="pending.iiko"
                                                    @click="importIiko"
                                                >
                                                    Выгрузить из iiko
                                                </BaseButton>
                                            </div>
                                        </form>
                                        <form
                                            v-if="formData.integration === 'accounting-system' && formData.accountingSystem && formData.accountingSystem.id === 'storehouse'"
                                            class="mb-4"
                                            :class="{
                                                '-preloader': pending.storehouse
                                            }"
                                            @submit.prevent="onSendStorehouse"
                                        >
                                            <div class="row">
                                                <div class="col-5">
                                                    <h4>Настройки Storehouse</h4>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Логин</label>
                                                        <input
                                                            v-model="settings.storehouse.login"
                                                            type="text"
                                                            placeholder="Логин"
                                                            :class="{
                                                                'is-invalid': errors.storehouse.login
                                                            }"
                                                        >
                                                        <label v-if="errors.storehouse.login" class="invalid-feedback">{{ errors.storehouse.login }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Пароль</label>
                                                        <input
                                                            v-model="settings.storehouse.password"
                                                            :class="{
                                                                'is-invalid': errors.storehouse.password
                                                            }"
                                                            type="text"
                                                            placeholder="Пароль"
                                                        >
                                                        <label v-if="errors.storehouse.password" class="invalid-feedback">{{ errors.storehouse.password }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Ip</label>
                                                        <input
                                                            v-model="settings.storehouse.ip"
                                                            :class="{
                                                                'is-invalid': errors.storehouse.ip
                                                            }"
                                                            type="text"
                                                            placeholder="Ip"
                                                        >
                                                        <label v-if="errors.storehouse.ip" class="invalid-feedback">{{ errors.storehouse.ip }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Порт</label>
                                                        <input
                                                            v-model="settings.storehouse.port"
                                                            :class="{
                                                                'is-invalid': errors.storehouse.port
                                                            }"
                                                            type="text"
                                                            placeholder="Порт"
                                                        >
                                                        <label v-if="errors.storehouse.port" class="invalid-feedback">{{ errors.storehouse.port }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Склады</label>
                                                        <SelectField
                                                            v-model="settings.storehouse.warehouseId"
                                                            :class="warehouses.loading && '-preloader'"
                                                            :options="warehouses.data || []"
                                                            track-by="id"
                                                            placeholder="Склады"
                                                            label="titleWithRid"
                                                            :error="errors.storehouse.warehouseId"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex">
                                                <BaseButton
                                                    class="mr-2"
                                                    type="submit"
                                                    :disabled="pending.storehouse"
                                                >
                                                    Сохранить
                                                </BaseButton>
                                                <BaseButton
                                                    :disabled="pending.storehouse"
                                                    @click="importStorehouse"
                                                >
                                                    Выгрузить из Storehouse
                                                </BaseButton>
                                            </div>
                                        </form>
                                        <form
                                            v-if="formData.integration === 'electronic-management' && formData.electronicManagement === 'diadoc'"
                                            class="mb-4"
                                            :class="{
                                                '-preloader': pending.diadoc
                                            }"
                                            @submit.prevent="onSendDiadoc"
                                        >
                                            <div class="row">
                                                <div class="col-5">
                                                    <h4>Настройки Диадок</h4>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Логин</label>
                                                        <input
                                                            v-model="settings.diadoc.login"
                                                            type="text"
                                                            placeholder="Логин"
                                                            :class="{
                                                                'is-invalid': errors.diadoc.login
                                                            }"
                                                        >
                                                        <label v-if="errors.diadoc.login" class="invalid-feedback">{{ errors.diadoc.login }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Пароль</label>
                                                        <input
                                                            v-model="settings.diadoc.password"
                                                            :class="{
                                                                'is-invalid': errors.diadoc.password
                                                            }"
                                                            type="text"
                                                            placeholder="Пароль"
                                                        >
                                                        <label v-if="errors.diadoc.password" class="invalid-feedback">{{ errors.diadoc.password }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Api-ключ</label>
                                                        <input
                                                            v-model="settings.diadoc.apiKey"
                                                            :class="{
                                                                'is-invalid': errors.diadoc.apiKey
                                                            }"
                                                            type="text"
                                                            placeholder="Api-ключ"
                                                        >
                                                        <label v-if="errors.diadoc.apiKey" class="invalid-feedback">{{ errors.diadoc.apiKey }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <BaseButton
                                                        type="submit"
                                                        :disabled="pending.diadoc"
                                                    >
                                                        Сохранить
                                                    </BaseButton>
                                                </div>
                                            </div>
                                        </form>
                                        <form
                                            v-if="formData.integration === 'electronic-management' && formData.electronicManagement === 'e-com'"
                                            class="mb-4"
                                            :class="{
                                                '-preloader': pending.docrobot
                                            }"
                                            @submit.prevent="onSendDokrobot"
                                        >
                                            <div class="row">
                                                <div class="col-5">
                                                    <h4>Настройки Е-Ком</h4>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Логин</label>
                                                        <input
                                                            v-model="settings.docrobot.login"
                                                            type="text"
                                                            placeholder="Логин"
                                                            :class="{
                                                                'is-invalid': errors.docrobot.login
                                                            }"
                                                        >
                                                        <label v-if="errors.docrobot.login" class="invalid-feedback">{{ errors.docrobot.login }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <div class="box__input">
                                                        <label>Пароль</label>
                                                        <input
                                                            v-model="settings.docrobot.password"
                                                            :class="{
                                                                'is-invalid': errors.docrobot.password
                                                            }"
                                                            type="text"
                                                            placeholder="Пароль"
                                                        >
                                                        <label v-if="errors.docrobot.password" class="invalid-feedback">{{ errors.docrobot.password }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5">
                                                    <BaseButton
                                                        type="submit"
                                                        :disabled="pending.docrobot"
                                                    >
                                                        Сохранить
                                                    </BaseButton>
                                                </div>
                                            </div>
                                        </form>
                                        <ListController
                                            v-if="formData.integration === 'electronic-management' && formData.electronicManagement === 'shops'"
                                            ref="shopsListController"
                                            v-model="shops"
                                            :fetch-api="fetchBuyerShops"
                                            :fetch-options="fetchOptions"
                                        >
                                            <template v-slot="{ items }">
                                                <BaseTable
                                                    :headers="table.headers"
                                                    :items="table.items"
                                                >
                                                    <template #edit="{ index, headers }">
                                                        <div :class="'col-' + headers[0].col">
                                                            {{ items[index].title }}
                                                        </div>
                                                        <div :class="'col-' + headers[1].col">
                                                            {{ items[index].address && items[index].address.title }}
                                                        </div>
                                                        <div :class="'col-' + headers[2].col">
                                                            <InputField
                                                                v-model="items[index].diadocExternalCode"
                                                                :placeholder="headers[2].title"
                                                            />
                                                        </div>
                                                        <div :class="'col-' + headers[3].col">
                                                            <InputField
                                                                v-model="items[index].docrobotExternalCode"
                                                                :placeholder="headers[3].title"
                                                            />
                                                        </div>
                                                        <div :class="'col-' + headers[4].col">
                                                            <div class="wraapper__button-nowrap">
                                                                <div class="btn btn__icon-purple">
                                                                    <button @click="doSaveShop(items[index].id)">
                                                                        <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template #actions="{ index }">
                                                        <div class="col-1">
                                                            <div class="wraapper__button-nowrap">
                                                                <div class="btn btn__icon-purple">
                                                                    <button @click="items[index].isEdit = true">
                                                                        <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </BaseTable>
                                            </template>
                                        </ListController>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import {
    fetchMercurySettings,
    setMercurySettings,
    fetchMercuryDoctors,
    addMercuryDoctor,
    deleteMercuryDoctor,
    changeMercuryDoctor
} from '@/api/mercury';
import { fetchIikoSettings, setIikoSettings, importIiko } from '@/api/iiko';
import { fetchStorehouseSettings, importStorehouse, setStorehouseSettings } from '@/api/storehouse';
import {
    fetchElectronicDocumentManagementDiadocSettings,
    fetchElectronicDocumentManagementDocrobotSettings,
    setElectronicDocumentManagementDiadocSettings,
    setElectronicDocumentManagementDocrobotSettings
} from '@/api/electronicDocumentManagement';
import validateMixin from '@/mixins/validateMixin';
import processError from '@/helpers/processError';
import { makeItemsEditable } from '@/normalizers/editable';
import { fetchWarehouses } from '@/api/warehouses';
import {
    fetchBuyerSelfShops,
    changeBuyerOrganizationShop
} from '@/api/buyer';
import { normalizeOrganizationShop, normalizeOrganizationShops } from '@/normalizers/organization';

export default {
    name: 'CompanyIntegrations',
    mixins: [
        validateMixin('doctorsData', 'doctorsErrors', {}),
        validateMixin('formDataDoctors', 'formDataErrors', {}),
        validateMixin('settingsMercury', 'errorsMercury', {}),
        validateMixin('settingsIiko', 'errorsIiko', {}),
        validateMixin('settingsStorehouse', 'errorsStorehouse', {}),
        validateMixin('settingsDocrobot', 'errorsDocrobot', {}),
        validateMixin('settingsDiadoc', 'errorsDiadoc', {})
    ],
    fetch() {
        return Promise.all([
            this.fetchMercurySettings(),
            this.fetchIikoSettings(),
            this.fetchDiadocSettings(),
            this.fetchDocrobot(),
            this.fetchWarehouses()
                .then(() => this.fetchStorehouse())
        ]);
    },
    data() {
        return {
            shops: {
                data: null,
                loading: false,
                cancel: null
            },
            doctors: {
                data: null,
                loading: false,
                cancel: null
            },
            warehouses: {
                data: null,
                pending: true
            },
            doctorsErrors: {
                doctors: {}
            },
            fetchDoctorsOptions: {
                limit: 8
            },
            formDataDoctors: {
                externalCode: '',
                veterinaryEmail: ''
            },
            formDataErrors: {},
            settings: {
                mercury: {
                    isAutoRepayment: true,
                    issuerId: '',
                    login: '',
                    password: '',
                    apiKey: '',
                    veterinaryLogin: ''
                },
                iiko: {
                    login: '',
                    password: ''
                },
                storehouse: {
                    login: '',
                    password: '',
                    warehouseId: null
                },
                diadoc: {
                    login: '',
                    password: '',
                    apiKey: ''
                },
                docrobot: {
                    login: '',
                    password: ''
                }
            },
            pending: {
                mercury: false,
                iiko: false,
                storehouse: false,
                diadoc: false,
                docrobot: false
            },
            errors: {
                mercury: {},
                iiko: {},
                storehouse: {},
                diadoc: {},
                docrobot: {}
            },
            formData: {
                integration: 'mercury',
                accountingSystem: '',
                egais: '',
                electronicManagement: ''
            },
            formInfo: {
                accountingSystem: {
                    options: [
                        {
                            id: 'iiko',
                            title: 'Iiko'
                        },
                        {
                            id: 'storehouse',
                            title: 'Storehouse'
                        }
                    ]
                }
            }
        };
    },
    computed: {
        fetchOptions() {
            return {
                limit: 8
            };
        },
        table() {
            let headers = [
                {
                    title: 'Торговая точка',
                    col: 3
                },
                {
                    title: 'Адрес',
                    col: 4
                },
                {
                    title: 'ДИАДОК',
                    col: 2
                },
                {
                    title: 'E-Сom',
                    col: 2
                },
                {
                    title: 'Действие',
                    col: 1
                }
            ];

            const items = this.shops.data.items.map(product => ({
                fields: [
                    product.title,
                    product.address?.title,
                    product.diadocExternalCode,
                    product.docrobotExternalCode
                ],
                isEdit: product.isEdit,
                loading: product.isLoading
            }));

            return {
                headers,
                items
            };
        },
        currentIntegrationSection() {
            return this.integrationsSections.find(item => item.id === this.formData.integration);
        },
        integrationsSections() {
            const items = [{
                title: 'Меркурий',
                id: 'mercury',
                children: []
            }];

            if (this.$auth.user.isBuyer) {
                items.push(
                    {
                        title: 'Учетные системы',
                        id: 'accounting-system',
                        children: []
                    },
                    {
                        title: 'Электронный документооборот',
                        id: 'electronic-management',
                        children: [
                            {
                                title: 'ДИАДОК',
                                id: 'diadoc'
                            },
                            {
                                title: 'E-Com',
                                id: 'e-com'
                            },
                            {
                                title: 'Торговые точки',
                                id: 'shops'
                            }
                        ]
                    }
                );
            }

            return items;
        },
        doctorsData() {
            return  {
                doctors: this.doctors.data?.items
            };
        },
        doctorsTable() {
            let headers = [
                {
                    title: 'ID хозяйствующего субъекта',
                    field: 'externalCode',
                    col: 5
                },
                {
                    title: 'Email',
                    field: 'veterinaryEmail',
                    col: 5
                }
            ];

            const items = this.doctors.data.items.map(item => ({
                fields: [
                    item.externalCode,
                    item.veterinaryEmail
                ],
                isEdit: item.isEdit,
                loading: item.isLoading
            }));

            return {
                headers,
                items
            };
        },
        settingsMercury() {
            return this.settings.mercury;
        },
        errorsMercury: {
            set(val) {
                this.errors.mercury = val;
            },
            get() {
                return this.errors.mercury;
            }
        },
        settingsIiko() {
            return this.settings.iiko;
        },
        errorsIiko: {
            set(val) {
                this.errors.iiko = val;
            },
            get() {
                return this.errors.iiko;
            }
        },
        settingsStorehouse() {
            return this.settings.storehouse;
        },
        errorsStorehouse: {
            set(val) {
                this.errors.storehouse = val;
            },
            get() {
                return this.errors.storehouse;
            }
        },
        settingsDiadoc() {
            return this.settings.diadoc;
        },
        errorsDiadoc: {
            set(val) {
                this.errors.diadoc = val;
            },
            get() {
                return this.errors.diadoc;
            }
        },
        settingsDocrobot() {
            return this.settings.docrobot;
        },
        errorsDocrobot: {
            set(val) {
                this.errors.docrobot = val;
            },
            get() {
                return this.errors.docrobot;
            }
        },
        accessToken() {
            return this.$auth?.user?.accessToken;
        }
    },
    methods: {
        async fetchBuyerShops(...config) {
            const data = await fetchBuyerSelfShops(...config);

            data.items = normalizeOrganizationShops(data.items);

            return data;
        },
        async doSaveShop(id) {
            if (!id) return;

            let itemIndex = this.shops.data.items.findIndex(item => item.id === id);
            let item = this.shops.data.items[itemIndex];

            item.isLoading = true;

            const address = item.address;
            const data = {
                title: item.title,
                address: address?.title,
                latitude: address?.latitude,
                longitude: address?.longitude,
                diadocExternalCode: item.diadocExternalCode,
                docrobotExternalCode: item.docrobotExternalCode
            };

            try {
                const result = await changeBuyerOrganizationShop(item.id, data);

                if (result) {
                    item = normalizeOrganizationShop(result);
                }

                this.shops.data.items.splice(itemIndex, 1, item);
            } catch (e) {
                console.log(e);
                processError(e, this.errors);
            }

            item.isLoading = false;
        },
        async fetchWarehouses() {
            let data = await fetchWarehouses();

            this.warehouses = { data, loading: false };
        },
        async fetchDoctors(...config) {
            const data = await fetchMercuryDoctors(...config);

            data.items = makeItemsEditable(data.items);

            return data;
        },
        async addMercuryDoctor() {
            this.doctors.loading = true;

            try {
                await addMercuryDoctor(this.formDataDoctors);
                this.$refs.listController.fetchData();
                this.resetFormDataDoctors();
            } catch (e) {
                processError(e, this.formDataErrors);
            }

            this.doctors.loading = false;
        },
        async deleteMercuryDoctor(item) {
            item.isLoading = true;

            try {
                await deleteMercuryDoctor(item.id);
                this.$refs.listController.fetchData();
            } catch (e) {
                processError(e, this.doctorsErrors);
            }

            item.isLoading = false;
        },
        resetFormDataDoctors() {
            this.formDataDoctors = {
                externalCode: '',
                veterinaryEmail: ''
            };
        },
        async changeMercuryDoctor(item, index) {
            item.isLoading = true;

            try {
                await changeMercuryDoctor(item.id, item);
                this.$refs.listController.fetchData();
                item.isEdit = false;
            } catch (error) {
                if (error.response.data.error.request) {
                    this.doctorsErrors.doctors[index] = error.response.data.error.request;
                } else {
                    processError(error, this.doctorsErrors);

                }
            }

            item.isLoading = false;
        },
        async fetchSettings(field, api, normalize = data => data) {
            this.pending[field] = true;
            try {
                return api()
                    .then(response => {
                        this.settings[field] = normalize(response);
                    })
                    .finally(() => {
                        this.pending[field] = false;
                    });
            } catch (e) {
                processError(e, this.errors[field]);
                console.log('Unable to get settings', e);
            }
        },
        async importIiko() {
            this.pending.iiko = true;

            try {
                await importIiko();
                this.$layer.alert({
                    title: 'Данные успешно получены'
                });
            } catch (e) {
                if (e?.response?.data?.error?.message) {
                    this.$layer.open('ErrorWithFeedbackLayer', { text: e?.response?.data?.error?.message });
                } else {
                    processError(e);
                }
            }

            this.pending.iiko = false;
        },
        async importStorehouse() {
            this.pending.storehouse = true;

            try {
                await importStorehouse();
                this.$layer.alert({
                    title: 'Ваши данные будут загружены в течение 1 минуты'
                });
            } catch (e) {
                if (e?.response?.data?.error?.message) {
                    this.$layer.open('ErrorWithFeedbackLayer', { text: e?.response?.data?.error?.message });
                } else {
                    processError(e);
                }
            }

            this.pending.storehouse = false;
        },
        async onSend(field, api, { data = null, normalize = data => data, successMessage = 'Настройки успешно сохранены' } = {}) {
            try {
                this.pending[field] = true;
                await api(data || this.settings[field])
                    .then((response) => {
                        this.settings[field] = normalize(response);
                        this.$layer.alert({
                            title: successMessage
                        });
                    })
                    .finally(() => {
                        this.pending[field] = false;
                    });
            } catch (e) {
                processError(e, this.errors[field]);
                console.log('Unable to save settings', e);
            }
        },
        fetchMercurySettings() {
            return this.fetchSettings('mercury', fetchMercurySettings);
        },
        fetchIikoSettings() {
            return this.fetchSettings('iiko', fetchIikoSettings);
        },
        fetchDiadocSettings() {
            return this.fetchSettings('diadoc', fetchElectronicDocumentManagementDiadocSettings);
        },
        fetchDocrobot() {
            return this.fetchSettings('docrobot', fetchElectronicDocumentManagementDocrobotSettings);
        },
        fetchStorehouse() {
            return this.fetchSettings('storehouse', fetchStorehouseSettings, this.normalizeStorehouse);
        },
        normalizeStorehouse(data) {
            return {
                ...data,
                warehouseId: this.warehouses.data.find(warehouse => warehouse.id === data.warehouseId)
            };
        },
        onSendMercury() {
            return this.onSend('mercury', setMercurySettings);
        },
        onSendIiko() {
            return this.onSend('iiko', setIikoSettings);
        },
        onSendDiadoc() {
            return this.onSend('diadoc', setElectronicDocumentManagementDiadocSettings);
        },
        onSendDokrobot() {
            return this.onSend('docrobot', setElectronicDocumentManagementDocrobotSettings);
        },
        onSendStorehouse() {
            return this.onSend('storehouse', setStorehouseSettings, {
                data: {
                    ...this.settings.storehouse,
                    warehouseId: this.settings.storehouse.warehouseId?.id || ''
                },
                normalize: this.normalizeStorehouse
            });

        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Интеграции'
            }
        ];
    }
};
</script>
<style scoped>
.code-wrap {
    display: inline-block;
    background-color: #5032C8;
    color: white;
    padding: 4px 8px 2px;
    border-radius: 4px;
}
</style>
