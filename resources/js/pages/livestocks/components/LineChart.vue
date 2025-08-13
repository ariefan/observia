<script setup lang="ts">
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Filler
} from 'chart.js'

import { ref, computed, onMounted } from 'vue'

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, Filler)

const props = defineProps({
  labels: Array,
  dataPoints: Array,
  label: String,
  xAxisLabel: {
    type: String,
    default: 'X Axis'
  },
  yAxisLabel: {
    type: String,
    default: 'Y Axis'
  }
})

const isDark = ref(false)
const chartKey = ref(0)

const updateDarkMode = () => {
  isDark.value = document.documentElement.classList.contains('dark')
  chartKey.value++ // 🔥 force rerender when theme changes
}

onMounted(() => {
  updateDarkMode()

  const observer = new MutationObserver(updateDarkMode)
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
})

const chartData = computed(() => ({
  labels: props.labels,
  datasets: [{
    label: props.label,
    data: props.dataPoints,
    borderColor: isDark.value ? '#63B3ED' : '#4FD1C5',
    tension: 0.4,
    fill: true,
    backgroundColor: isDark.value ? 'rgba(99,179,237,0.2)' : 'rgba(79,209,197,0.2)',
    pointBackgroundColor: isDark.value ? '#63B3ED' : '#4FD1C5',
  }]
}))

const chartOptions = computed(() => ({
  responsive: true,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: isDark.value ? '#1A202C' : '#EDF2F7',
      titleColor: isDark.value ? '#E2E8F0' : '#2D3748',
      bodyColor: isDark.value ? '#CBD5E0' : '#4A5568',
    },
  },
  scales: {
    x: {
      ticks: {
        color: isDark.value ? '#E2E8F0' : '#2D3748',
      },
      title: {
        display: true,
        text: props.xAxisLabel,
        color: isDark.value ? '#E2E8F0' : '#2D3748',
        font: {
          size: 14,
          weight: 'bold'
        }
      },
      grid: {
        color: isDark.value ? '#2D3748' : '#CBD5E0'
      }
    },
    y: {
      beginAtZero: true,
      ticks: {
        color: isDark.value ? '#E2E8F0' : '#2D3748',
      },
      title: {
        display: true,
        text: props.yAxisLabel,
        color: isDark.value ? '#E2E8F0' : '#2D3748',
        font: {
          size: 14,
          weight: 'bold'
        }
      },
      grid: {
        color: isDark.value ? '#2D3748' : '#CBD5E0'
      }
    }
  }
}))
</script>

<template>
  <Line :key="chartKey" :data="chartData" :options="chartOptions" />
</template>
