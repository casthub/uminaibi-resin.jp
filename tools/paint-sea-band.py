# 各ページの見出しの帯：空と海の絵画調（横長）。下の端はページの地の色（#eef8fa）へ
# 使い方: python3 tools/paint-sea-band.py site/images/paint-sea-band.svg
import random, sys
import os; exec(open(os.path.join(os.path.dirname(os.path.abspath(__file__)), 'paint-kouri-bridge.py')).read().split("o = []")[0].replace("OUT = sys.argv[1]","OUT = ''"))
R.seed(7)
W, H, HZ = 1200, 420, 330
SKYB = [(0, '#0d4aac'), (0.8, '#1352b8'), (0.97, '#1a5ec4'), (1, '#5d9ee4')]
o = [f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {W} {H}" preserveAspectRatio="xMidYMax slice">',
'''<defs>
<linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">''' + ''.join(f'<stop offset="{p}" stop-color="{c}"/>' for p, c in SKYB) + '''</linearGradient>
<linearGradient id="sea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1595c9"/><stop offset="0.25" stop-color="#18b6d8"/><stop offset="0.7" stop-color="#2ed6d6"/><stop offset="1" stop-color="#5fe0d4"/></linearGradient>
<filter id="brush" x="-5%" y="-5%" width="110%" height="110%"><feTurbulence type="fractalNoise" baseFrequency="0.035 0.09" numOctaves="2" seed="3" result="n"/><feDisplacementMap in="SourceGraphic" in2="n" scale="5" xChannelSelector="R" yChannelSelector="G"/></filter>
<filter id="rough" x="-5%" y="-20%" width="110%" height="140%"><feTurbulence type="fractalNoise" baseFrequency="0.06" numOctaves="3" seed="8" result="n"/><feDisplacementMap in="SourceGraphic" in2="n" scale="6" xChannelSelector="R" yChannelSelector="G"/></filter>
<filter id="canvas" x="0" y="0" width="100%" height="100%"><feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" seed="11"/><feColorMatrix values="0 0 0 0 0.5  0 0 0 0 0.5  0 0 0 0 0.5  0 0 0 -1.1 0.75"/></filter>
</defs>''', f'<rect width="{W}" height="{HZ+4}" fill="url(#sky)"/>']
g = []
for _ in range(300):
    y = R.random() * (HZ - 4); L = R.uniform(70, 240); x = R.uniform(-150, W)
    c = jit(grad(SKYB, y / HZ), 12)
    if R.random() < 0.12: c = mix(c, '#ffffff', R.uniform(0.15, 0.3))
    g.append(f'<path d="M{x:.0f} {y:.0f} Q{x+L/2:.0f} {y+R.uniform(-12,6):.0f} {x+L:.0f} {y+R.uniform(-6,6):.0f}" stroke="{c}" stroke-width="{R.uniform(5,16):.0f}" opacity="{R.uniform(0.12,0.28):.2f}"/>')
o.append('<g fill="none" stroke-linecap="round" filter="url(#brush)">' + ''.join(g) + '</g>')
# 遠くの島（右）
o.append(f'<path d="M820 {HZ+2} C870 {HZ-14} 940 {HZ-24} 1010 {HZ-24} C1070 {HZ-22} 1120 {HZ-12} 1160 {HZ+2} Z" fill="#3d7a44" filter="url(#rough)"/>')
o.append(f'<rect y="{HZ}" width="{W}" height="{H-HZ}" fill="url(#sea)"/>')
g = []
for _ in range(160):
    t = R.random(); y = HZ + 4 + t * (H - HZ - 30); s_ = 0.4 + t
    L = R.uniform(60, 220) * s_; x = R.uniform(-100, W)
    c = jit(mix('#18b6d8', '#46dccf', t), 16)
    r = R.random()
    if r < 0.25: c = mix(c, '#ffffff', R.uniform(0.35, 0.6))
    elif r < 0.4: c = mix(c, '#0f7fa0', R.uniform(0.3, 0.5))
    g.append(f'<path d="M{x:.0f} {y:.0f} Q{x+L/2:.0f} {y+R.uniform(-2,2):.0f} {x+L:.0f} {y:.0f}" stroke="{c}" stroke-width="{R.uniform(3,8)*s_:.1f}" opacity="{R.uniform(0.2,0.45):.2f}"/>')
o.append('<g fill="none" stroke-linecap="round" filter="url(#brush)">' + ''.join(g) + '</g>')
# 白い波と、ページの地の色
o.append(f'<path d="M0 {H-34} C200 {H-48} 420 {H-22} 640 {H-38} S1020 {H-50} 1200 {H-32} L1200 {H} L0 {H} Z" fill="#ffffff" opacity="0.75" filter="url(#rough)"/>')
o.append(f'<path d="M0 {H-22} C220 {H-34} 430 {H-10} 660 {H-24} S1030 {H-34} 1200 {H-20} L1200 {H} L0 {H} Z" fill="#eef8fa" filter="url(#rough)"/>')
o.append(f'<rect width="{W}" height="{H-26}" filter="url(#canvas)" opacity="0.3" style="mix-blend-mode:multiply"/>')
o.append('</svg>')
open(sys.argv[1], 'w').write('\n'.join(o))
