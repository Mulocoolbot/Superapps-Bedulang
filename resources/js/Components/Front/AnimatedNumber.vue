<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
    value: {
        type: [Number, String],
        required: true,
    },
    duration: {
        type: Number,
        default: 800, // Animation duration in milliseconds
    },
    format: {
        type: Function,
        default: (val) => new Intl.NumberFormat('id-ID').format(Math.round(val)),
    }
})

const displayValue = ref(0)

const animateValue = (start, end, duration) => {
    if (start === end) {
        displayValue.value = end
        return
    }

    let startTime = null

    const step = (timestamp) => {
        if (!startTime) startTime = timestamp
        const elapsed = timestamp - startTime
        const progress = Math.min(elapsed / duration, 1)
        
        // easeOutQuad easing
        const ease = progress * (2 - progress)
        displayValue.value = start + ease * (end - start)

        if (progress < 1) {
            window.requestAnimationFrame(step)
        } else {
            displayValue.value = end
        }
    }

    window.requestAnimationFrame(step)
}

const parseNumber = (val) => {
    if (typeof val === 'number') return val
    if (typeof val === 'string') {
        // Strip out dots (thousands separator in id-ID) and parse
        const cleaned = val.replace(/\./g, '')
        const num = parseFloat(cleaned)
        return isNaN(num) ? 0 : num
    }
    return 0
}

watch(() => props.value, (newVal, oldVal) => {
    const start = parseNumber(oldVal ?? 0)
    const end = parseNumber(newVal)
    animateValue(start, end, props.duration)
})

onMounted(() => {
    const end = parseNumber(props.value)
    animateValue(0, end, props.duration)
})
</script>

<template>
    <span>{{ format(displayValue) }}</span>
</template>
