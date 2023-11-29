<template>
    <div class="box__app">
        <Navigation />
        <div class="box__app__content">
            <TheHeader />
            <div class="box__app__main">
                <ClientOnly>
                    <PerfectScrollbar
                        id="main-scroll"
                        ref="scroll"
                        :options="{
                            wheelSpeed: 0.8,
                            wheelPropagation: true,
                            minScrollbarLength: 20
                        }"
                        tag="main"
                        role="main"
                    >
                        <Nuxt />
                    </PerfectScrollbar>
                </ClientOnly>
            </div>
            <TheFooter />
        </div>
    </div>
</template>

<script>
import '@/assets/styles/layout.css';

export default {
    watch: {
        $route() {
            if (!this.$refs.scroll?.$el) return;

            this.$refs.scroll.$el.scrollTop = 0;
        }
    }
};
</script>

