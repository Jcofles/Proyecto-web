const { Jimp, ResizeStrategy, HorizontalAlign, VerticalAlign } = require('jimp');
const resizePlugin = require('@jimp/plugin-resize');
const containPlugin = require('@jimp/plugin-contain');
const pngToIco = require('png-to-ico');
const fs = require('fs');

Object.assign(Jimp.prototype, resizePlugin.methods, containPlugin.methods);

const input = 'icono app/UniMaps.png';
const outputs = [
  { path: 'itfip-map/public/icon-192x192.png', size: 192 },
  { path: 'itfip-map/public/icon-512x512.png', size: 512 },
  { path: 'itfip-map/public/icon.png', size: 512 },
  { path: 'Clase1/public/icon-192x192.png', size: 192 },
  { path: 'Clase1/public/icon-512x512.png', size: 512 },
  { path: 'Clase1/public/icon.png', size: 512 }
];

(async () => {
  const image = await Jimp.read(input);
  image.background = 0xffffffff;
  image.contain({
    w: 1024,
    h: 1024,
    align: HorizontalAlign.CENTER | VerticalAlign.MIDDLE,
    mode: ResizeStrategy.BICUBIC
  });

  await Promise.all(outputs.map(async (o) => {
    const clone = image.clone();
    clone.resize({ w: o.size, h: o.size, mode: ResizeStrategy.BICUBIC });
    await new Promise((resolve, reject) => clone.write(o.path, err => err ? reject(err) : resolve()));
  }));

  const icoBuffer = await pngToIco([
    'Clase1/public/icon-192x192.png',
    'Clase1/public/icon-512x512.png'
  ]);
  fs.writeFileSync('Clase1/public/favicon.ico', icoBuffer);
  fs.writeFileSync('itfip-map/public/favicon.ico', icoBuffer);
  console.log('Iconos generados correctamente');
})();
