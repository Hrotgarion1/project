<template>
<div class="w-64 h-48 flex-col mt-4 mb-8 ml-4 justify-start items-start gap-6 inline-flex">
  <div class="w-64 text-gray-500 text-xl font-normal font-['Arial'] leading-loose dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{$t('Contact us')}}</div>
  <div class="flex-col justify-start items-start gap-6 flex">
    <div class="justify-start items-center gap-2 inline-flex">
      <div class="w-6 h-6 relative">
        <i class="fa-solid fa-envelope" style="color: #293c5b;"></i>
      </div>
      <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus" @click="openEmail" @mouseover="changeCursor">
        hello@wintowinjobs.com
      </div>
    </div>
    <div class="justify-start items-start gap-2 inline-flex">
      <div class="w-6 h-6 relative">
        <i class="fa-solid fa-phone" style="color: #25395b;"></i>
      </div>
      <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">+34 692 65 14 41</div>
    </div>
    <div class="justify-start items-start gap-2 inline-flex">
      <div class="w-6 h-6 relative">
        <i class="fa-solid fa-map-location-dot" style="color: #25395b;"></i>
      </div>
      <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus" @click="openGoogleMaps" @mouseover="changeCursor">
        Tribala 48, Pineda de Mar, España 08397
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
const openEmail = () => {
  const email = "hello@wintowinjobs.com";
  const subject = "Tengo una consulta";
  const body = "Hola,\n\nEstaremos encantados de poder dar una respuesta a cualquier consulta que tengas. Esperamos poder responderte lo antes posible, un saludo de antemano :)";
  const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  window.location.href = mailtoLink;
};

const changeCursor = () => {
  document.body.style.cursor = "pointer";
};

document.addEventListener("mouseout", () => {
  document.body.style.cursor = "default";
});

const openGoogleMaps = () => {
  const address = "Tribala 48, Pineda de Mar, 08397, España";
  const googleMapsLink = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`;

  window.open(googleMapsLink, "_blank");
};
</script>

<style scoped>

</style>