<template>
  <svg :width="width" :height="svgHeight" viewBox="0 0 90 260" fill="none" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer">
      <!-- Barra de Verificados (opaca, debajo) -->
      <g opacity="1" class="cursor-pointer">
          <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              :d="`M0 ${verifiedYBase}H36V${verifiedYBase + 10}L0 ${verifiedYBase}Z`"
              :fill="verifiedColor"
          />
          <rect
              x="0"
              :y="verifiedYBar"
              width="35.5"
              :height="verifiedBarHeight"
              :fill="verifiedColor"
          />
      </g>
      <!-- Parte derecha -->
      <g opacity="0.9" class="cursor-pointer">
          <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              :d="`M72 ${verifiedYBase}H36V${verifiedYBase + 10}L72 ${verifiedYBase}Z`"
              :fill="verifiedColor"
          />
          <rect
              x="36"
              :y="verifiedYBar"
              width="35.5"
              :height="verifiedBarHeight"
              :fill="verifiedColor"
          />
      </g>
      <!-- Parte superior inclinada -->
      <rect
          width="36.6015"
          height="36.6015"
          :transform="`matrix(0.962788, 0.270256, -0.962788, 0.270256, 36, ${verifiedYTop})`"
          :fill="verifiedColor"
          class="cursor-pointer"
      />

      <!-- Barra de Propuestos (translúcida, encima) -->
      <g opacity="0.4" class="cursor-pointer">
          <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              :d="`M0 ${proposedYBase}H36V${proposedYBase + 10}L0 ${proposedYBase}Z`"
              :fill="proposedColor"
          />
          <rect
              x="0"
              :y="proposedYBar"
              width="35.5"
              :height="proposedBarHeight"
              :fill="proposedColor"
          />
      </g>
      <!-- Parte derecha -->
      <g opacity="0.4" class="cursor-pointer">
          <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              :d="`M72 ${proposedYBase}H36V${proposedYBase + 10}L72 ${proposedYBase}Z`"
              :fill="proposedColor"
          />
          <rect
              x="36"
              :y="proposedYBar"
              width="35.5"
              :height="proposedBarHeight"
              :fill="proposedColor"
          />
      </g>
      <!-- Parte superior inclinada -->
      <rect
          width="36.6015"
          height="36.6015"
          :transform="`matrix(0.962788, 0.270256, -0.962788, 0.270256, 36, ${proposedYTop})`"
          :fill="proposedColor"
          class="cursor-pointer"
      />
  </svg>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  proposedHeight: {
      type: Number,
      required: true,
  },
  verifiedHeight: {
      type: Number,
      required: true,
  },
  maxHeight: {
      type: Number,
      default: 98,
  },
  proposedColor: {
      type: String,
      default: '#8BC34A',
  },
  verifiedColor: {
      type: String,
      default: '#3E8E41',
  },
  width: {
      type: Number,
      default: 90,
  },
  svgHeight: {
      type: Number,
      default: 260,
  },
  areaName: {
      type: String,
      default: '',
  },
});

const proposedBarHeight = computed(() => {
  return Math.max(1, Math.min(props.proposedHeight, props.maxHeight));
});

const verifiedBarHeight = computed(() => {
  return Math.max(1, Math.min(props.verifiedHeight, props.maxHeight));
});

const verifiedYBar = computed(() => {
  const value = props.svgHeight - 47 - verifiedBarHeight.value - 30;
  return value;
});

const verifiedYBase = computed(() => {
  const value = verifiedYBar.value + verifiedBarHeight.value;
  return value;
});

const verifiedYTop = computed(() => {
  const value = verifiedYBar.value - 10;
  return value;
});

const proposedYBar = computed(() => {
  const value = verifiedYBar.value - (proposedBarHeight.value - verifiedBarHeight.value);
  return value;
});

const proposedYBase = computed(() => {
  const value = proposedYBar.value + proposedBarHeight.value;
  return value;
});

const proposedYTop = computed(() => {
  const value = proposedYBar.value - 10;
  return value;
});
</script>