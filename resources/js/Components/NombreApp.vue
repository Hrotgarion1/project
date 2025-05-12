<template>
  <div>
    <img :src="imageUrl" alt="LogoLargo">
  </div>
</template>

<script>
export default {
  name: "NombreApp",
  data() {
    return {
      imageUrl: "/assets/img/LogoLargo.png"
    };
  },
  
};
</script>

