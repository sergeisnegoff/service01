<template>
    <div class="box__app">
        <Navigation mode="empty" />
        <div class="box__app__content">
            <div class="box__app__main">
                <ClientOnly>
                    <perfect-scrollbar
                        :options="{
                            wheelSpeed: 0.8,
                            wheelPropagation: true,
                            minScrollbarLength: 20
                        }"
                        tag="main"
                        role="main"
                    >
                        <Nuxt />
                    </perfect-scrollbar>
                </ClientOnly>
            </div>
            <TheFooter />
        </div>
    </div>
</template>

<script>
import '@/assets/styles/layout.css';

export default {
    name: 'AuthLayout'
};
</script>

