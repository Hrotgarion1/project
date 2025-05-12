<script setup>
import { computed, ref, onMounted, getCurrentInstance } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  type: { type: String, required: true },
  label: { type: String, required: false },
});

const page = usePage();
const user = page.props.auth.user;

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const isLocal = computed(() => {
  return page.props.appEnv === 'local';
});

const canRequest = computed(() => {
  const roles = page.props.auth.user.roles.map(role => role.name);
  return roles.includes('invitado');
});

const requestLimitReached = ref(false);

const checkRequestLimit = async () => {
  if (isLocal.value) {
    requestLimitReached.value = false;
    return;
  }
  try {
    const response = await axios.get('/api/check-identity-request-limit');
    requestLimitReached.value = response.data.limitReached;
  } catch (error) {
    console.error('Error verificando límite:', error);
    requestLimitReached.value = true;
  }
};

onMounted(checkRequestLimit);

const roleNameMap = {
  'tipo A': 'Cebolla',
  'tipo B': 'Tomate',
  'tipo C': 'Lechuga',
  'tipo D': 'Endivia',
  'tipo E': 'Pepino',
  'tipo F': 'Pimiento',
  'tipo G': 'Ajo',
  'tipo H': 'Sal',
};

const displayText = computed(() => {
  const mappedRole = roleNameMap[props.type] || props.type;
  console.log('Type:', props.type, 'Mapped Role:', mappedRole);
  // Si hay label, reemplazamos el type original por el rol traducido
  return props.label ? props.label.replace(props.type, mappedRole) : $t('Request') + ' ' + mappedRole;
});
</script>

<template>
  <div>
    <Link
      v-if="canRequest && !requestLimitReached"
      :href="route('identity.request.form', { type: props.type })"
      class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
    >
      {{ displayText }}
    </Link>
    <button
      v-else
      disabled
      class="px-4 py-2 bg-gray-400 text-white rounded cursor-not-allowed"
      :title="canRequest ? $t('Daily limit of 2 requests reached') : $t('Only guests can request')"
    >
      {{ displayText }}
    </button>
  </div>
</template>