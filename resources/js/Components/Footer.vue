<!-- resources/js/Components/Footer.vue -->

<template>
    <div class="border border-gray-500 dark:border-gray-400 bg-white dark:bg-gray-900">
      <div class="overlap-2">
        <div class="grid lg:grid-cols-4 gap-4 md:grid-cols-2">
          <FooterCol1 />
          <FooterCol2 />
          <FooterCol3 />
          <FooterCol4 />
        </div>
        
        <SubFooter />
      </div>
    </div>
  </template>
  
  <script setup>
  import FooterCol1 from '@/Components/FooterCol1.vue';
  import SubFooter from '@/Components/SubFooter.vue';
  import FooterCol2 from '@/Components/FooterCol2.vue';
  import FooterCol3 from '@/Components/FooterCol3.vue';
  import FooterCol4 from '@/Components/FooterCol4.vue';
  </script>
  
  <style scoped>
  .footer-big {
    height: auto;
    overflow: hidden;
    width: 100%;
  }
  
  .footer-big .overlap-2 {
    position: relative;
  }
  </style>
  