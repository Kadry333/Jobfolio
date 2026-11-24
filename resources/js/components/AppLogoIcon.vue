<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/images/logo-icon.png"
        alt="jobfolio Logo"
        :class="['inline-block object-contain', className, $attrs.class]"
        v-bind="$attrs"
    />
</template>
