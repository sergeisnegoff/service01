<template>
    <section class="box__suppliers__page">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1>
                        {{ page.title }}
                    </h1>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="box__integration">
                        <div class="row">
                            <div class="col-12">
                                <div class="box__token margin-15">
                                    Ваш токен доступа: <span> {{ accessToken }} </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-7">
                                <div
                                    class="box__deacription margin-15"
                                    v-html="page.text"
                                >
                                </div>
                                <div class="btn margin-30">
                                    <a target="_blank" href="/openapi">
                                        API Системы
                                    </a>
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
export default {
    storageItems: [
        'systemApiCommon'
    ],
    computed: {
        page() {
            return this.systemApiCommon || {};
        },
        accessToken() {
            return this.$auth?.user?.accessToken;
        }
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
