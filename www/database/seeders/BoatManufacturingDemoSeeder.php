<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class BoatManufacturingDemoSeeder extends Seeder
{
    private const COMPANY_ID = 3;

    private const DEMO_DATE = '2026-08-06';

    private const BOAT_BOM_SOURCE_GZIP_BASE64 = 'H4sICCoVdGoAA3Bhc3RlZC10ZXh0LnR4dADNfctyI0ly4Bn7FWF1UbUWBDMe+RozHRJAgoVpAIlBAhxOX9bQVbBurvhosYpjpTlptIcxHXTSF4ztQaaDzOaiHxj+2LpH5CMiEZGZANm9arNGolgs90gPf7tH+DKbpotssN5kkzTPs0G+G1ffJ9l0fpUNlsk23cyTxeA322k62K3m0wSek+U1/v+/ttk2WfyP2YLF3s1isEiW81Xy8qeX/wP/PMkn2YB63ijgg6t0QVbZZp3B4yohzIP/SE7Gm2Q1yf6ObNIc/h3Js8VukmRkvpoBpOQ6XQwEG/r+4NurgaBDLgZ0FPrRMPY6MFIBy15tE8IBzQ2hAh7L5YAFQxYjsGhI+YBRPoxoF6RokK8/pJt0km1Skq8JGypQ3pB6gyUbcG/o8UHg8WHYuap4sE0n82lGaORFAx4PIw9XQ9mQiYGIwiHnXSD84sWEr78YDYbwykglMfTFIKRR95tFEYDK84TAdubz64RQj3lktZssUtyBdDOBTSeL9Doli/lvdrDsob4x8N6wmL//YcD9YcRhY7wIfiI6kPJ4kObr3TIh63RLpvAjQCvfIEJi3LNB4A8DCuAY50PRRVFGS4pyBhSlsAtxIEmKWwtQBKxOsC4owlgU8ozARRHY821KplkO/89zskjg6wD4UC0UlgzcFOHORx0YwmBQsDhIwDzNQaIIwM5m25c/LuaThFyQGfx5ki3xRTzC/AHn0VBw+S7h0GcDfxSzeBjHVkyLDKRqA3+U2HyK8jbJkq1EUkgZMomQosRgxTGwHx2yoAc4hzCJYnlSlngw9PwesJhXbhgTKAIRShG+owdCDTIOu2UXARPMGfQMAAOryQn8GoQuBoO3TdfJJsnb39/T3p8Nw7APLFMFUApao1YBVPAh6wOGR8di5MOagqEveZN68TAIQA/0XNZZ/FlqL8WeMb6BXQyyRXKVLm3vz0C78/r9WQi6xM6SOpAzVstAL0b1agVor8ChFjREr1SRTG5HpSJjEQ+5fTcm2eo6zduEt7CDPxTCC+wLJOMuglfQXIaQDgOB0CTzUiB73Lkw0HqmJRTD0vYIyXSF7QHS+i7S1utymNUY9cF9aVVZyIe8E1ZDp8RA5RjfTekUNEzAqt2UclhWYExJd/V2vvCHYeeKXsk3VAxjjjgV3/hcDAO73te3x6ESwGb4mkoAcoDe9zrBnSVjccFVhZCNeAg8YreN63metRoroHNQ8ztCcuxhBcnB60BNj9W8Dp5B3LEkF3NS9PFq7hSgvUUHqFdygl8wX+FjRVEnOV1swE3LwFEm7MavJkPDVgEIuSOFrQqCzsWcY6bhlXnNQ3SENtGz+7LbdNvFQ1TnIXABgg5ILh5CX7TmIS9yOWI1IDcP6RqOxwDZbvEqUGdQ0Q/whSsqxrE/9OzS8yHZTLfZupWMDFzomowMgAn72+vAHJQEpzaoCBlELtdZhyREyYfTlMzINeFgikC5q3iBxkNBIRwDtrbzs7EoVzQH/7jelDByKUgdViSjBrJ5+dcrCIlxbVLi0nyFr/zt1eWSDwn8RppLCRyS9YJQxrwbSn0PgotkkoyTJZlO5gOvQI/2LgBJC8SQ2RlVXwDoolLUrydkGnkkUEHLEuJpCNH9wi6HAZJKgB50RKgGhRoOGsh4LfPM6Z4bhHml9QPqe7XSC8DwOnxCgxinCwkqFlYLSehzl2lfJ7DshZsZQUAUlSQvisgVC2lw3JzIA40TgcsdOlYD1dwypm9Z6HLtNAhvysrwZjUrg0vgiDE19GfsHa0YU+4dA1PsEH8IckAYIIAn42T1IZ1vWk0GNywG+MWOmMYB1aH0YBf9WumBc3cSTIdjirBqvxTobtcYLqgm06B3VTONH7vCUAc0l9fBTK+DgRfnSBE5AJ/DGlzGlBVr8MDl8G2T5Tohk2R+I3keGWDTmkGBfYw1Qwh+tJ1KbsBOBmFaODaM7TRqAWvnEW/ItdjFnVxpAWyyiSfTFSWbREPf4U454bkYBUjpq5hNRSnoup1KhDfVYtzXtBh4PKHD33Ou5pwAynB9OcTCbds1RbzL9owfeH0avwpXzuMYoJNPOa34FIW7L7hGKAFun2ai6DDquy4X/8hQv1Y0ceSKmI9BnpP7GlLNcfCBrm28Cm+NERH4OAB61Wp+QA/QesPEMG6liwWuc9+82uv2OvbNBrapBrgWCQr404nw3NuovIdSDcTD8FTKnrGdmAvUQk7PZaAbwr5O5zdpuzMRefVuxrC5vZRIDdcVjeoJjciVIXNDdTkUIq6NBUT31O7EuuE2/dBYE3Kwwo6g0QnvlXGEQN+jzp0E1BVTORfgNlaGV4Ph2qnvdgaXxoWPUHApvI+jDKqQbpPVb3YpRoPjXb6dXxe+ttNSBGJQFYbaDY8DslP1QMhUFEmc6ZdWuI2qKdqOUvMEYDFPh/jaABUzqmV0GruC09YVuDkLosmKr2TO/3Tg5+SL4C0Glf5jw7CNqmtwcTqsWMC1UoXvCoeb4JwcRAPNOXbEk0fAmiYrDrXkJbgc/db0Sl7xkEkqJcTiod9v9c46uVfoHlUm58yVxW0CPCeQQgx1HBW1608gUroCHlhn6w6fNNRdHOSVU6A6TSKtA6i43dAew3TkYmBtfp2LAWfEUddygW1yYOFlSA4EmrZ5NkfA3B5TaFR8g2HYxmFHcF9tZJluZH3Q9m0h8RH6c9J2hV0vsnYtnS5odUuMaH07OZNHWrUARMvVjeMC7DaBddTE3U0+TrDODJDmsPnMFS20AG5yqPB0tz5y1ISd8M7aTabnYLnLnIImAwKMs3lHgkZo6Tv05B3ZJhOa2/DoWRlXgq0By5WKCbTNAo3iIG4TmlM1Ma1gEbgqZA1oZxmBSNsfTtuZrF9UxLRqEigsh1PYPx5i6L1UWQnu0v42gM4sfKSRNxJD6upE64qC+vodbxMjUF5k/MrcJ3fVDxS+STIGHd+uFGMthhXOtNwRPKdM+bUuBLo6un2OoL0uZj2Cd5aPzDS7EzuTv8lmeQE/vfC8dk3la24QljPsdG1Ae5WmasJyaSplBYqkcehKbjWgnaVbuFakZqJdcJUuu0427ZqRYvWwrq/j5wlAXyO8DVhnsZivSW8cusRDYdriu5MeBXyvaNwoeI07i1oOsG6m8zWmY47aqQuok/uExn2xq0XTBfYsNhR6vQjC1TYTN/kAji8q6rHqi++fzgXAbVbECrhfPtfRQdIG11kw0oqK1JnYboF8Vv7V12q54LU5+ooU1vRmOwfHc9PhBwrDD/TblIAB0c3sTKO4q13SAu91pTkLxLNYnOmhPPyxLfsxBhom065YSauEgpvhMDnHAJ30jWsLFrYnBQxwTgfZ12P3oD121yG2pV58LfXibH87BvnqhGOgdQH6cX9in1XbElqaOfBc4WoPJulb/+jBHmxIQ61fGQjSDckleLHm3gjP1eplgGqmoKkWJ/PQVS/qw6fMcFeA3A6v0mCntyx0x3rnGROBq5z0SrbCAzmh7uxxMGEuvzLZzDOs0fZSRCzUFJHnitQcUN117lobuc9QOGA6GU+LVYQzpnRAPU+UQ0OUw1pvrDcv/3KxzGCZeNhhnC3HipGyDa4BqEHBJo8f77/fk08Hsn58evm/j4BiuX/6uCeb57sD/AFP8ZGr9QeyfPx0uHsErTYCnMDP68PLv+8HURxgVYmNgijW0wbdqEESkucvj/cvf/5y+/ERF/B9uZKf1Eru63XcI3LC/UTHzXyBGiMMmN6j0o6ZIZeJwcfbH/ZPT3vyfn+3f7o/kM+PD49PjyquUMB9mWqVn/1fahT7gzyR8vo3L3+82oH7tNuAKQA5nV/B7+Bv03f6O1DQLBhzY1AfnkA9JvnvapfONwn5Nlkls0V6Qy4oSb7bLcBlTlY5dluttukgRmzyCEcAalXE/TnER0YPBsn3T3tY66fDLVBsdnf4SmiMnOHjXsXk/p7cPjx+BekSI4WsoCCeRAN0ngtdPgcGBxOsKDXNJqmiYagtBMQh323m3yV4ZJSPfGDFJei5a/L+5Z/Kf0VA1yaLKxCib/QdpCOPMSxElF9OXIanURjbfvBvZ+ABk+18nYE2vpGKl4wXQAtQ+JLy0rGV1A7hveHTOC7ZCy9oUHiT9TaZAsx0dQVuN9kka2mY/Cr4LSQgxHS2/DwRiWhHQmCPL5nBqcLH7Cbyq+diVDsy8IMGk2ybgZcjBaCJznwlcDMDT32ehkVQbb9QAFCrAmswUNDrfI7vQ+Zj+EsZo8EWQeRN0d3SwoZORL5S/RaJ4BcgFCgPmjgow18KOvbTsEAvu3aiE2gT2KCoAQfeglynK88LvISsku1ukyx00gkQccy2y8dpSOCdJj8+/x7e5/bhx0fwW4AJtiBj1W+ZkuV7wyAsHichoh4Q72q3kM2fJAU35eVP6B1nN7KlXQWbF4RH6MHgP6S4bwZu1RWsHqdxCI9sHEJL/qg55G8V3y+xPgRSDCbOO0GIfeVMHvMIZxc+PdKZJa4YO1pZNPTjk1AxCGU2aF+AniBOBatIiuLLEv8yekcWNwRPyWmsAnFOqD5PUxzUs9FwVkkZoFM0HJXvFQ3V0czwVBKeZnhYw/Dg2ZETKDmYPD4cvqLXkT78sP9yAMf7zz/dfnok2zQFh+jhh2e5COrf3+uoMElT66lfXsqqPmppsYIyZ5Mt0m0GGxT6HrpuaC8THXQQSq9DPXqAprAZpV9TezWTy7Rwa/ilMHV4jCfz1aMPeGHlqquKqwB+obsLpqIy3cmp3gHqgH8yL53ESsZrgChaXuNDbYIgoEE/TWFYFkdJeNSDSr4KL4/fgvkXPGqXB3DBQjxxQ12Wx+Ci0Ktl4W7/BR/PyK1Pj5/BGw9Bvg9KQui7UcMXYVx99kEDsgDmC1knugGXWAfEZCMz68GaqP8gopWAKiNytUkXHwrLYQIG3wUESj16cT1tePObFCI+c7UUVDY4LOrR58XxiHVShPQa1IYIBTEejZWfvVbKwAhcgdbZHAkjBSnx4+LRZ4GBzDwowZ7NbzKyuVwmkw8S8DuIdfFhkFUUGt7pdBhLZUENfnM5S5cpSDoIzPbl3zAiDm8iRGISOfSxIVB+ulHIKPoU55Y2nVv52QNBg3dZk3cBjt9joT8/KfprpEjXSNT39Q48NyGArybZKr3BF6hc/Nj761/I7HLWWLiQbCjaubBaOPUcTEiPKALiHPnqswPwyUpUNJQoD7rpgl6uYEV2aJxtNrBsiEMWSb7NlukGsziUlzEPRCHYiKk+j/2IEmYMCxfRAGz6SoWj2XiDWWMIbyS/ZBiWzmR1pkvdWYkS0MH0Eajy6Rb+Idj1j/ufQNXDy4KowDv7QJX3AZntNln+jZG9iDGR7Ed6/thKEECQLPGinUmKQgls/vLHHLnwhgbyFh4a4b0dGmQvwAZ49WhdPIvFIN+t5S0+Oo5tsiRraRUMY8hibCfwjcPdtiUz1CKTzXwxn2TkKl1Iuhd/T+Dn+TZZkIB5Q3bDBEC7ifA13gNA04kToXwP+ejYBGbZBHAO5CbEQzZ6L4od0MPKELuhuBjGtAM6h58vQPZTfCOwPwuwO8mW4P0wRBYVckksQ2RVj5hx6YuNViNQZOvdjdS3yTjHrM0WFMIlKJxrkyUjeY4bP90AK1DTIo6ZFgvUsqWEmfGMww+vQM5S1B1lQqnIxDVf1wRil+7FNIfoc51eJWSJYKrsraahQ9lkqB7tPOaD3KX5JFnlanFp872YF6F6UI+W95OURhCr3y2yFWhM+DlZv/zLCTTC9XA2SJ7u90+3j2S8f/i4/7Qn+49Pt3eYoJ093x/I/eHT6FckAl8i/MqjYBh/jUA1NJmeRbLXXD3eiul5k+l9pqdMT+dL1uRLP9DPqFkJFIe1Uihz+BVLimAobjj3b+IbTAzOdstUtyAhrhdQxfpBktOXLZrLxttOghbmOI3MUZPM4EPolTirevfZ4Nv5FiiynU92eIOaEtyxrN5IOn97+wXcohiPPsTUcJVt6+XgKUgbN0VzCU8gNxABLN18oe9bIORNInQEoPSm5GPPEzcPTP91ApIGynwLdjgbkl8DeQE0xMdDDB3WyWQLP02XZJJukiXgNrh6FFOK553KLy3oTg86tW2VQSejuofnwBDafBl2wVl7QBhi2YQK/WopO8UEr32OBTAfOhrI8LITCsMW09+Qp8HUo2PlP5fDUS0dfWtU+sl0B2vPQVK3ZFLkF9P8N7t0Yyptn8tuB/XooAoLi84krAqMN/MNcswymUoyzTIIPqdILkDSsAwCIgdVGCy+tCN6M7OqIK43U88Lo8iqwVRClgX8RoCrImoFpqEBfc+o+uza3jd3ZYqbJBA4L1XDy58wBzVJwDOAXw79qtReJrmwl4NyX6/+WwCyGKKYDwgFb/sA1pYlWoCMSySyvCs/eAM+lefpsBXZwu0FeNxFEGbFLpNsXShG6kWNQo7AcF9+OmHJyFML50on1L+kAQZFUmqKeO5SZcvknTPyhgaKBxrddMV1gnEa7/IcncTNfDv5MAdq+N7ISPKICBu3iocTGsMATsjQEBTqhuzysczMMIjGgXmn4Gr614QPhek3yXvh5GfrOmM6uIZFrrapzYkLAzwdUzzaVshAZNSfyBI0mRSI2QIUHJryyL+Ef0jeU8oYSMINCUAgvjHDWVkbUI+29TJYTSVxekcjkAeInRIiIH5gXzHojr7KLiRyOU5+d8mEd2lWPgI8JKcerRR6E72hSdzbi3PRpY2MAiGqPNq92a2RV7LVRTabkfcK/TcE6T+UGWwaJeQ63SzTxYeskbuBCEv41gCrRgTkLPebQly+TQpZBONI3i9235kebIy3KshPJ0zpMINs3+0fHms3ebL//vbhQMZ3+49/T9a3+HfKaQ4Cb+h/9QOw7rDLIKlHbrMfYlRaPNrQYq/Z7PDxx/2n56f9Z/LTHmz/+vHpC3yfHj7f3f5h//Dl8JnkL//xdHsAr+D5848kf3z+8iOsbzm78NgFpd5FYEQaEVhfMK3q0YYcN0yFGxUzo/W7WqXg/Bkbw7ElJCgeTpCogSNgsM3vFvMV+S2wF9lki2mySgjIl6Fv/WGICte4kc8KjlvBsSNwcSjBxe4XluDo4Go3B5MOP0ggLJWgwemEv5M5nKXqAeZ4vbHFLdHYBahRmAL5M1LpBTS1BLg8DCGkAq/WwSEUXL54GPrVl/+2XMKBka/nV3PMfM3XW3Tjr9PtLpc138VuOV+B3yFidkPjEBTehbEzYDJ9LM6jo+1ZvQ3tLcFHvdrfPn/Zk6fDp8cHCFmZ16hMjEBO8VKT8kvrboN7Vof3xm6NL7nwtIo1uu1G3QLix6h4OFHUrV9p5TNqeY1pha1fhkOTSwiESq2/SafZCqKwHKitZFWaSS3GCIQkiB/qCU2beo78Umsy1JpZmmtqU9JnB/5ITkzj6OFVU/LzZ9D9rKn7Y+MAiL3WO63O0L/867Wyk9QLejcjXIHPctSLgIk/zM6IqLuro4n/tNI9M2jrh4OI6uW8TmwBJqX9wXb/8A/PB+L7/E7pg8P999hk9/LvL/8FqqCZfBoJT0iWLr6cho/p7Raqu0ImG/VfHKnbw/DKzAj7Hzj2pp74XhCO1Xiw/+AIS1k1ZqigMYDxW2r2TRSyPh3DrqarXAonaIUrsCWqHDaZr74zmhI5mnBHeqCFFxlF7xsrBRN0RkmEEUfAMfbwG1WUmKlPB8NdpdgWxJV0jXe/Bj8nuwaIyN+TUnNBxID5DmwTGGFyFYK3DFg9EMvlSP5Nlqtjv3kGPLnFFrtkgSlFefBJU9XgzsD+qUfrgmQJIx6Mn/83VpQ/HcinpwP4Rs2yJ0ILumDJ3Acb7C7G2WKLVbh36IiSEAIhszKkeCpyt3hp8GgJD2uDdnhAdsyHUz0f7iA+Rill7TipirxVSYuZVUNUw3TgU6HPMXBtq7MqrZtQVZVWgFshYkNsgKHadDfHiGpFOJOVDPrumxFOwFCSg81rIhxSV9GxXl5IB9sdCEmOXhfWqHCLCwEM8EAMjfWucCfzxgpMml+B1SHg8xQlSjCvIpSXQYauep+CIm0iHWB+HYhl8D52V+P5Cw4sfrVJdrnBNYLhySv16Nzn02rBx4zHZWEGM1IYkWhCOi+oB8sNG22fAYZtgTVqq+AyIFKywBRJWUZppBwEHgEV+tE7y+IAj5ZskWDQIN6EQg4i0CPJGHYWDxhYIpdayvpHklRzVlQoyYSw5uaq3X6bdH9NwQAM5ma+msiDOtkNsMtFsxkR762lxaMVFCtjJrRNyqvEGxWO0hd4/CdWn21vCj546Zqu5ui1FHkwPKdXlJx1b48G8phOgG61kxmF0tBFqQ2ZJl+nKhcGfwId+z5ZbefwHeCvMf1iBq8Ci83ys5UBfo7Eby3v4OOUtWBVCYOV42FdssDTi4lRPKCekC25jtpaBRRUDd5cipneHKvs+AuLOnSjvs+G9GvQEtjTUQwxPaq+4ksbNnDL9SxRhQcPwJJttlaxIkdh+BpFmFKwocQKXaA+25CxuOIj22FPhetXAsX7K8XbR742W9B9lbOwpyw00fQr0UTfuqpXSpXyHi/4M7fbo7jfHLfIuTfSbPMBxAXT3RXY1JFHBLsEzwzPPlXAlRag/g2VTUp1ohBvXsRZU7FxlbNl7TF2AuBpKnUXSkZKjFJ4DTuOJ81wXS1ihmoVu6BXGUCCUBBiJ+5Jl1BNEtI7twK8/UU9WqX2tRnm2uyCf6PMrkrUYmqU5PNZGZxwb2LmwGHfqey9cWpSXuzSy5/vfv98J8/3fN6//Oen4qQPiD/DrsJ3ZpcGHjaVV7854Qp1RmO8+w7PS01360VxMmO7Ad8aXaL3VTzXyNzKu/3U4xj6XGYninNaGKRClL/YfYc7hLf1EyADpp79315AYKrOEhQdQgLL/iI07uxrAcsNsPJkSQGUNYHiUAjbvh0BZdVa5VirfF4YbXmQrji+ghjUMZWC1oE6mWCvIh+h8HHd+COEVZ0VklH65HKcbeXvadSWt05H8TCwSEQTONYgFs9/QM542P/+8AMGphAtjMeXY4zE5bmfUg+a+Vom4y716IOnIj6GNStwbkAYF+mUvJ+vg7DRhs3VtVfcmt1rgmZAy2PQy0S2ZmI+JsVv40xtg67xQhk5yscxmqJmnSgUoszJ4G3a2PU5H+/meEKq/DWyvvAYeZ9S4PLZ4oJhaG+maCCmF3JeUvmlHeeIDiY/wpaQ/HB3+PL4tCfju2f8057k//j5y+Ee/UIQ58+3RS7hojwwuPrrXwgEf/QiNSScRwxvHQrxoLUlSWTgBivViRxsPXzMZg20/CIlRjIQdC9WsNSjAy+40KUhXGZYRx7jyVTKlmM8x2LmZTFxIaov7YDPzHtpZ+uKmodnXNXjwBUZuJYZmrFklWbkfbb6RiIu04UmXnmejTdxynqqxVy+wfudhwtcVhCBX+/w6gRQpZslOkuRZ6YpwC/FHZOPjk2PAws8yjwjguBhiNsdor/bxUXgPhwDDMwFco/j/BX16ILnDyb7269FMp/Mnj/f/v5wRyaP3x/wzxGZ3D59fL798vi5EhOzjOija6UeHbQFu20imR7I+unxy+Hj/rFCQ1IQyiesfS33T7cPt3/Yf3rU7SIrUmKxfqGGHR+4yDLBtgLvcCFPtIKgXeExlmvUm8nqAypTTBkul0xjHK88dSl90Ti0ul8mprAPJioxKRdyQFlYYkGvDHw9W47PxBL1xYIpl7q4WZ0iVf6fpx9Od6hn0QsTwtVJN1L3HOMprUhOf+Jd3Ed7bRGmBuCVLq6nl4mchyRfRyaTA97Jdzjt7QQ+UNvDaKgxgcCynsVjMtHQE9ltmuLrMIYXKiCiyJfdsR32GmeP9sAjir0pXqfIdlFsYhHG3SsOJMEpSMxiykl4evF0cMRoOIiz5AGMaLrwhL02R3L0BQqP5DTYdVaxM1ZTW9V9n9S+rvSRsTrcM3mpgucCqW1tmYSDX46tlb1tgtkX9LIH+nepyb+dbwcxSIlUE+UXd0clvCQdlHd57lZz2JQ8WRR1kmSzMZYU4eWlcvJb0NK7SwXwQbqayDpBJ0gPY0+8zdx3pbdnmFXCL5jFQR2zw4pE2fNZZ27B4fWid3rqERwc7KvFYZl4YWYP+Nj5c7WDMCLBOtskHZvtJKOAqiJL8aUL4mKaQ6xapWfQ01b5ZaNwPqJ4Q0dcfemCilnDkOPQbgwPN1gYfvlnOXnkqEwHBKBh8eixWC82CtzrxCwcoMyoqzU6aYnA6GBRBDmbejgKmTXWSEFtCAzpyy99QPNB2blxtZuvsIG/CVYt84TVihJkfgzLE2ht1aMPlwaocEGiVHuszObi3l+yyDczukyeVZWPPmsMpJCV9yfrUhTi9T3q0QXI1tGIM66XGTBTmht9kr5UI3TEvZYDEBbI8CXP51XTHLY1rFLTq8XvvA9UzOX5oW3VeM/NV/CX8YPc3xtVB09KPmPuM6cmAt+GIJClVvmxlMZd65NGtRV6rrMXVafuW3e7cDCV5gVx5ZDxgOMNSWSVbUDHkBWIMV7Q45G8uCvp70hxgVCeLXYT2eNWXTGGp73VtVgUgxc+wiKazFW58TnuTsKZ3vXlhBzWLR3HFjj2+5J8vC+svjCJgavUSYLGRV0Ygmk3dQWgBv32tTQHysdxOcVEDpRnoxAslUwOtCzCddVXZFzaHIliMJkb0msvZ2d4pqK+nX3kY2DXTkHnJLdy8eUA2ZEXFwOoW6gpDGDIJ8JTMyqxDINKZ5rNc4LHQnJ11TDer0cj3HnsYxOqkOvGcM6N4xEeqq/vxYpGflBMWmriMYfOu24gC43rVQXeThd3AnMOlVfDG5XsCHXOox1SY/x14JX348u73QI/ttPQBHIGHbHNKNAn9QqpHy2ozFnzjjf3QU3UF0Nz1XvWAakxVcwr2L24rpcF6jqidiAuhvdNfvdB1TMbj5jQzhtNH2gXS+No+ti2bH0wfXN6o6xd1eMbeWRnHB3EOROIvBJNseN+GLjWWg0Sf6VVUqJVWSUPokOrBu8cec/j4sZVJVqs6AtsA+TQ48p1qfR47FEVSraBsps45hdXKBe3wI4oKy4wbINl7n0YF/cKqr2P4c9+B4TXDiUvh2GXZiXGJI6d3+rx9K5bVRkzJtqAGgGmola70j3s3jfHAAZOE6uBCo9AMU82OYECkGdnPO+bgV8sEt89GLBYdG75Ofc1Yhe7Zpn8kV9W6ZuIqhHrrqtXqTllAVwBq0aoh7075uqUtlJdvIplftvW1GDsjM7NO+V9qg64ucE0RslhIUi7cRX4zap2qn//Wg43Gbyc0e5E52JHXsz1Ln0mYJuwdd3OS+2jYsyPUjl+QNW5Wyegc25ohRAv1OcgApCid6mJphpN7+A+Vo7wVtwX4GAG23tXcFzcx4qNUNznFU3jbjAuz9vU2AG+qU1H1oCa80ojzbyyYliD81+fQfzQM+ZZ0xFjxbXCTSwf1L0MsuPo9RZWFlq/vSoMLJ4f820bdYzUeU9zHCA8dXGusDtgFmgNeaeSE69KgoNpiG0CCIDSm3r052sIIeQd2xUhILaxensNjE4vHhRQSQU8I2+LBxqgnAZSrmxZXTuOxWkHUY2VNTg4lNdeVxSlscpkWaDow1RfQ1Ff3pJdUpThTWkOivYYYOvLibNXVXTg27RfE1SDBLE0IhUJhKeKTRYo4CfvVm8jXdSULupgYx2jW7R8Q7RsGqwJ6kiuDBJg3OtYkLr25dUUUHO9SwoIrnqK2/G5R/x51ftHdgegAag5497TXl44/OTibOsbbL2WT1PnE9uxObfd06asBCq/2wbmpIvm2wA1bWCsuWBo2G2egfbv3RMLueEUMXueUYN01rBcoU3SkWdQbOoO78HGPqn6noPXx6yF412oPZxEblN7DsxO3g+1EahCnVPsC9IRe+JRsdolx1kvVh/XAdRkDjMGxay7NedrATZY78h7eapoJXsnk/85/gaHrUjhkW6bZ9d0joW5I0Ma6OmcwFctez3BnpPXCcDZ1/I6QWzPOJTHO6+y3WaZbm3khSiW1+SNhLo6qRcgVw6jiGeL8SjYLGbbMBtIp6NCi2inCOQ9YdfRVphOXeHpusLBCjaI52xYWMzpLDYsKmrGTXTNG990tK+zlcU8T2UrHR5aG3bnNAtVSCmmWXAb87SCdQ3fY4E2fzRQp05PAXzOJpVTdNUe+WqO5lEopk/CebMNYsUEdrVBECIE1iDSids9ErG28MLulLYAdW2OF6PkFNNGIjVlsz/YlilIkSblPlM3lvUH/OpZSPrAYcFUy01/9OcNHNZC86g4wHFUB2hc+fZ2TOdr/gRqVWsqpQ29k+8CfRSnlZCtYF2cF0a1WghjddPhKYDPmpxI9cmJDs09zrCxZ5YscryjLEveIGsQavUJGgf2/J4Vr6tOMRR1BRCrrrZtsQN02nph2HrmsPVWmM3JV1wbiCmoPUlihXRORjIoYp9yAj21m0QDXzmL+hcI3qx4+4Zx/QG+KqCzgzwK7SIttCtu6+4F6bzQTJihmbUloe5rJEXP03yRtaeLPby3pPZzrK0erWCbZFHCXabM7EmnNoAut5aabq16nLpYl/bV50oCV1idsjbA52Svy4F4akuBg7q2VJ3Ta3kPHIajNf/EscvRs4FsZL3wmFy9j7G6868fJOd0SWxYrlRqcc9bL5Dn6cFI14Ox7+qDwoO6ZTOvPH/3BspQ4DWqtTIMhb305EbuzufV0hrbKdgC1FVoLLIvxQBb7mo+c8E9q3CGt/3V7B/66i4Cu1NaIn0jQ9XLOXRgfoVf6IL4GpfQAfOVAQMrGhtVwBAXQ7Z64nbnJYw+A0zynUCls2KQWNMAUetbKOauGq5fzWRe4R4UeQlPXRbaH7c7K8G18eGR3bg6gbqGbAaBbjbUzc/9wb46OvW0Sb04XcHug7jQt+TMfT1nHrVJkA3wWZkWlY5SLMeL6zU6UK7T+U36BiZHaBxHI9/u9ztxOy2OV091jXqxhgbTZXA83eBgy6Y7KWQB+9oW3yJSKTpVQmov6DnRuz1Uo3MFbZroIaA14DPtqGfaUWt4qXAWt04Ul6XNr9+kZsc1totbhcyBvl+Kz1p4bIfbLGXqxTjw8ls2xwHx1WbV1zRdTNXNNSetoF/nHS06n1TnXVzMWzsJ0VkRjdBbodDPd7MinhdBVlLo2xqj5N2XVV8UHnt2v4wFqtuSal1SrlDSBdNlSH398EVg74BzQ22G0lSvDwpXKO2Adtbsa67pEj+0N58ojGuwkq8v/HrGCRq8FsmdyK8xOrdUO0ND7UHMESiT5thGrtG81cOqgbxSLXioDSq1AP6X72abGmlLsUHvDeaYl+sB7hzbY+T4WWjPz9Vlhext8see0d+ONxu596iB1s04QmMc2l710eG59IDRuXsKYY74MdT4MVYXdfcC9GqeDDQfidE289vA3MKYQmNMUI+sN1nOcsWZZ7jiLepzism8PCs74cgiy5PVVWu7bhngKavEHDmTbvCvDvRagb8+4GsF32TWQMsZgifoZpk2qO54LtTd6zCwd/l3I/gF49W2ZZxXyNc0Ls7Ac0tQE/Vvdsl0k0w7mFpoByBaPa1W8G4962ls7dm7ifrAd/E109uzmL13qA/8JmMz4yiFOh59BtyWVEWP7r4eCF6t9YXG2tRxbqzHOs7yJhjztCIXo22hpLzXf9l6xrTM8ypm9uO2KF8H9+oMrwHs9cldA9xRtU3vL0WHqBcYdwYjMjIYcdh3Yb9gwllHe17djZt1N8eRfTmUTV4Dep28QUaYyfMNdX08tidqrHidLBnWMaxvby62w3O1tyoBVFwZO04OWSGe1X7CtApQ7AjYFJ5xNn+LwEHojSeR3atqIHxFyNCE9JpgoQHrPP3KDe1qbe5+86R0r16fU9LRWowPjqv1OPIJiWiuOwwitucfbACbHoLQDrsJO2e9TcY3wOFztf6i3G6K1otkm8C/XRZX6YyzJTaj/FIpm1b07oSvbmGtx37a4To7D4zGbd+el2wH3dxvX78jRQ02OAngWd4/1+6OEI708THWDUL//7bvNfY33XYN7Bvvugb5TTa9hvez7fnR4FHakVCPNM+Yefbz/g6g7oQFFZGes7DeoOIC6kxUMF1Ds9B+It8B9SwryQNf90vwtlXrFSZHs17bSA5mS6d4YG8HcsB0UzzUOvfsF604ILrIHWr1C/DFWS9is1ek6LRqufDsTWb69Aa8FXCbrd9AmUXaqQQsQLt9z2PMrygpOUG+pqLkAnoW/+vXGXBHg6x9usVbOOhG11ybN2ZF7nbWawvj2Q+CtUF17Y3qnC27GNSklhPgnrE9WMnVnD9fTeKy40xvtnO8I/vV21KdWZLb4jPXXW5HSHu2HLvDbxOaaxsiXWk5Lg6wwDtLYYW6worspqHYcjV2sitLRYtbWpRlgOVbe4/tIJ30FXVsyu2HmBwAnYfWNBL7ob2ryQ7yrDK0roMC317g0xytNF/jnchlTyrZvQHLU61HFEcQWI/Tdi3BbSgCTQCsSdZu2EcN20xLBUb27pZOqL/ISbPOVbjz5EZ+MgrtecJO+GcxpdBEP3CcqlHj796mT7lfnbuJ0W0Cdd/cGrocgXLaPa16iOeNbFb6CFgzluJac4uwnxVqwvhFatpNpG5eFDovCm73K5rgzjL6oX7NUTFX6Ej9ZiucUvd6rvM0z9hxZZaGy8lvnu5yue7nq6C4WE1PSFP72WENzFnEpXo6zd4pZwT1ZceM26CHhkG353NtEN32nGr2vMsUafB6BhXWPgwrxL7th1xrPwxcl8ZYl3xk1QLDqtlT9jZIZ5WGfN275txeQNaG1xVjEI255r9QnaLPKl5RvOgF/jUVjT4IfrYyhw25usRkmc4Tskpe70Kev4uWhbzxRtowvPVeWnD8bNu5zZbJVA3KSLfzxYekGtz2Btuon+SL1TipE/G7bWSo2Uh7f1oHZGcjjLZrkaOPtx30WbZUv2eEYjqxrQxWzoKTw9/a0tVU67QTbcGnBajbBupHZbk9XHJCPYuTqXazEfPa8h46RhwH30Yc5tfECex3/TiBuvPAGnE4s/cHOqGeRRydc8DOWw+DHmFkrIs4kU4ca0bWCdSdb9LSfjxoy4ZZoJ5FnEjvZNXuATZnc2B9URshqmYnRYPx4/33cjjs+vEJp39e4Fy5j3uyeb47wB9QFZKr9Ydy0CP1RvrkwCjGexcGbBREcZ3u6kbs8UHy/OXx/uXPX3CUHaD/vlzHT2od9/Uq7hE14X6iY5ZTaMJBGLD68GQ7XhxzBXvy8faH/dPTnrzf3+2f7g/k8+PD49OjMWRZ3Wem3SDQ+UI4JjBPcC7L9G9e/ni1A8W521zLBMP86kXN9KbvjJnLPFSzDbDLoDfdmOS6q528/+nbZJXIUV0XVI6PxDnPqxxN62qbqvmVS3kxBpfNC2E/Msk5z8Eg+f5pD+v8dLgFWs3uDl8JjZEffNyjmNzfk9uHx69gs8RIH5WJh9rwoi3PjiyfA0uDI65oNM0mqaJeqC0DBCDfbebfySMrfOQD+y3VMNqXfyr/FUlJniyuwEI1Rn97jOGF3+WXkxbhabTFs574t3KGrxx0vMaZaBDak/EC6DDBqfc4srMaHhhiTIvja6hd/hxYo3AAb7HeypFOKXgf25RskrWMcZrTvxk23KnPk1CIdhQE9vaSvWtMBA1iyaOenTntqKjHIODeZuAaSZZvIjNfh8vLvOXnKTgg3K33abspbjtKgTU9ss7n+C5kPoa/VGMxl9jTRzGtU1VDOtH4SsFbZIBfgBigBGgCYMwX4mq8c5167ESGo7VDNihO2gXeglynK88LPHSNtzt5y4U+TztAo64ep6DAMW8/Pv8e3uX24cdHnLNIlluQqeq3GnOYPcxUqMcJaNAFAsFZqNovDmQqp4LLSfSq0nVBeARsJwWM4n6ZE1OxhFQ8TuELHtn4gpZcUfPF3w6KCZkcbxnBflKvt8j6KgtxzBmcXfj0SDeWmGJ0KMFxqeKTPogYj9EbWCMlp+XpPUVLfFHiX0bvyOKGmJPV8DaBUH2eoiSoZ6PerJIqQKaoNyrfKRqqUSvhacQ7zbSwhmmh2mnVTnyDyePD4St6E+nDD/svB7J5+fNPt58eyTaF2HP/8MOzXAL17+91RPqlmL+0VFVXpEqLFJRls2yBYwkTnMeJzhhaQ2NaXRBKb0I9OgFT2ITSW6l9lcllWjgr/FKYejpGr1Y9uoELKyddVZwE0Av9XDASlbUM8FyDjqWfzD8nsI/xCiB4llf4UJsYiCHQ8yrnudIYk7A86qSPrzrSjt+A+Rc8auf/GBMCNKpLwC2cE3o179/tv+DjGfnz6fEzeNUhyPJBSQR9N2p4GIyrz24kwPtgnJBdohtwbnUwshQuPzvpwYJIgamMxNUmxXwDVV6JDhaPU4XFoweX04ZPvkkhXjNXSkEpgxOiHt2vzL0SZEo0mA2BCWJsbJafPVbJQMlfgXbZHAkexWguLh7diwvkbDglwrP5TUY2l8sEB3nKabg38mGQUxQa3OFIGMtkQQ18czlLlynINIjH9uXfsN0rvIkQhUnc0MfalPx0IZDjbU9xUmnTSZWfneAbvMqavApQ/M5F/txE6K93Il3vUN+vZ7u5SQCcNMlW6Q0uvnLSY++vfyGzy1lj0UIynmjju2rR4DnY2Y4e0QIEN/LVZyvYk9WkaKhJHnRRBH1VwYoczjjbbGDJmONM8m22TDHPCc5+Ga9ADMEw6yU/m55BCTGGRYtoAHZazaidZuMNJokhNJE8kmEoOaumSrtVmpUcAR1MH4Een27hn4Gt/rj/CRQ5vCYIBrytD/R4H5DZbpPl3xh5Bnk5pK/dNWQlBYBPltlmm05SFEBg65c/5sh3NzSQ8yVphHMeNLhegPf0qEfLwlksBvkOazcQUWsYtsmSrKXON4wcHjeOcblVa4htuQy1xWQzX8wnGWlehw0/z7fJggTMG7Ibhn1YNxG+wnsWe6Y7hmdkWfFoJT6zEB/MvSR+PGSj96KgvB4Khljj4WIY01bYHH6+AClP8W3AtizApiRqnk8xekOSyRBP1RfB6tqsjUo4pH69u5EaNRnnah46rB4Uy7XJhBGqEfnpAlcBmhYRyLRYHH4pKd+Y8Wz1oyuAs3RSdN5gwqfIkTVfVAdhl2Oc4RxpI7arkemaBg7xsGjxaOMqH2QszSfJKlcLS4+Gf3sRqgH1cL6ZpC8CWP1uka1AJ8LPyfrlX3rTBtfC2SB5ut8/3T6S8f7h4/7Tnuw/Pt3eYbp09nx/IPeHT6NfkQjH2X3lUTCMv0agAposziJ/GAXF421YnDdZ3Gd1CvN0PmRNPvSDuiJlJU0c1sLfvPSaiWAobjj3b+IbTNbNdstUtw4hrhUQxfUl66cvWTSXTPW2m1eSN2qSl2KXiN+mvPEk83wLtNjOJzuc/KtEdFwcdMYrwG6/gIsTY+dFTDVX17ZWDpZf2q4pGkF4Apnh9cGCzRfGJHPwmRgOqwSeq2tA1ondDIz5dQJSVdQQsyH5NZAVAEMkO0Snf51MtvDTdEkm6SZZyjKjRuVRTOWVyuUXJ7LTQ0RtM2WIiJPSOuCHNs+EXXDWHsCFWLDAUTM2pV3TSvDah1gAu6HjgAwuz9ViuGH6D0zSRT5aV/3zOBDVstE3RpWeTHew7hykcksmRb4vzX+zSzemUsY5nODuqkcrPVhYHGHAnPx4M98glyyTqSTQLINQcYqEAhQNvS/A61ezGoovbWjeyFgqeOvN1PPCKLLqqeJYTMBvBDgeolZTGhLQ54yqz/ZNfWPHpBgRhaB5qQRe/oS5oUkCtj72bkK/uim9TD3hvXWU+/X5HAs4FkP08QFhwC7ifWSyEApwcXlEllDlB29Ap7IDnvH6sFETOO4diG3RHZStC+VHvahRPJGnjeWnA5KME7UArHQk/UsayO41lJEiArtUGSzZNConP1G88MpFT6Ga48a7PEdXbzPfTj7MgQ6+NzLSLzggB+8ilA8HLIYhl5ChHKjMDdnlY5k1YRA1A7NOwV30rwkfCtMHivFCcfnZssaYDq5hgTg1yuKMhQFeqlM83KtjIB7qT2QJ2kqy/2wBSgzNc+Rfgrkg7yllDPj+hgTA/t+YoafMygv9CnDLWhmspJIu/YgTEAaInBIiwPNnXzE4jr7KK6bI5Tj53SUT3qVZb8AZguYoQRtt3kA/aNL11oJbtMUhc0A4KW9d2ezWyB/Z6iKbzch7hfwbgnQfygwyjRJynW6W2ALfyKt4TH260TD0yNUuU4ift0khd2D0yPvF7jvTC42xVVd+OiBKlxek+G7/8Fg7upP997cPBzK+23/8e7K+xb9Tbm8QeEP/K44cE7C3IJVHjq8fYgxZPNxIsWt8dvj44/7T89P+M/lpD/Z8/fj0Bb5PD5/vbv+wf/hy+Ezyl/94uj2ApX/+/CPJH5+//AirW84uPHZBqXcRGFFCBFYVTKZ6uFHjRqlQoWJftGtXqxTcOGNDODZXBMXDARB1bARMtfndYr4ivwWWIptsMU1WCQFpMjQqzt4AlUrr++KswLgVGDsCFocSWOx6VQmMDq52czDT8IMEgkgJGMfde6p8rk4IDzg2Qh65GRqLABX0NlBSaQA0oAS4OgwhEALP1MEVFJy3eBj61Zf/lpzBgXGv51dzzEfN11t0w6/T7S6X9dTFbjlfgSchYnZD4xDU2oWxI2AMfSx4o6vsWfwH7Q3B07za3z5/2ZOnw6fHBwgxmdeoB4xAKvFAXvmlZY/B0arDcGOXxpfwb7RKMLrdRrUgwNtv1MOBoDpXjF2jhe+nZR6mFa4+OQhNBiGAKbX6Jp1mK4idcqCykktpALX4IBCSFH5YJxdtCjjyS83IUDNmaa6pRkmZHXgYOTHNnjx0LLRbdd5Ot7Ombo+9unfdXkWdVnf+vvzrtbKA1At6l/avwAs5quxjKg6zJyLq6o5oYj+tGM4MqvrhAKccHWkVF64AE8P+YLt/+IfnA/F9fqdk/3D/Pbakvfz7y3+B2DcTQyPhyRHV5ZdTsDG9cUH1Kcjkn/6LI3kUFlP+eFclBycgsARkrVhCDQtW849wlPVYPEjBMfjwnVXwJgJZ941hN9NVLkURNMAV2ApVeprMV98ZzXt4K83AGsi38B+j6EFjln6CbiWJMF4IOEYOfqN2ETP1aWUyNTiGK2ka734Nnkt2DfCQoyelhgKPH7MSWHYfYaITgq4MmDsQy+VI/k2WqzEneQZ8uMWGtGSBaT555ZWmjnFOrlc8WpYjiwfxYPz8v7FW++lAPj0dwNtpFhYRVtAOSWYo2GB3Mc4WW6x3vUOXkoQQxJi1GMVHkastSoNGS2hYgbNDw8FlanyZvRukIjrGGGVVNqkKqFUBiZm1OVS1dOBTUXfWuzbTWe3VjaOq9iqwLfCwVTTAEGu6m2MstCKcyQoCfffNCLuzlZxgqxeE8NRe2KuXFtLBdgcikaMPhRUh3NZC2AKcQ0Hj+sylk1ljBSTNr8CmEPBhiiIg3vsALhjV7lSxwZDWjg4wvw1EMjgde43xNCQn6jxibvCJAAIGxaNjb0+psh4zGpelEMwVYTShieO8oBosNWy0RAYYbAWWWKuCyoA4yQKTGGXpopEYEHiIRdRj1C0LAxxaMkQCQUN3Ewo5V0uP/WLYzZDXJWmLPPWP/ajmfKjgjwlhyZdVO/wWqfaacgGYwc18NZEHZbMbYJCLZrMeLAd8e/VoAcTKSAdtjvIN5YHrZooBD9/G6tP9juBBl+7lao4+SJGdwn76opCre2004NgiH6Bb7GA+obRvUdBCNslxzlhRlAIN+j5ZbefwHaCvMT1ihpoCS7jys2XT3z79Wss1+CtljVXVnGDVePlied7K0IHgn2CLqrWGpUDKagJu1XgzzyVBrufTjRGS+iNPhW7lF+fiIDY0rqCpQja8i4Zss7WK3Tgy9tcowrDeFrlxOTtQfrpRsbjiDNu9KgrTrwSK6VfqM/hstlj7KmtgSxpoQuZXQoaeb1Xvk4rhPd57a26hR3EPuSecmyjNLB+Axz7dXYEVHHlEsEvwn/DsXAVaSTP1b6hs26kTcwI9HjoKYzAJRyFtve5Y6GfxwIsu8UkxNOwu3raEq3KKDKpF7PVdZQAHQjOIZ7gn3bYkN7MXFNibxsWjRf5el8OtDSX4IcpQqnQoJiFJPp+VIQP3JmaGGfaayp4Uhybkxd68/Pnu98938ozK5/3Lf34qTquAGDPsqHtn9jFwjMHBSWcOqEKdOBjvvsPTPtPdelGcM9huwO9F1+V9FV818qN4NUHxaMKey+xAccoIA0aItBe773Bftsg7QABM7vq/vYAgUfXHF10zAkvkAqcd9QDKDaDyjEQBkjVBUhDp4906AsmqdU5QjeXzwszKs1rFMQyEr45bFDQOVK+9rfZ6hMDHNeOPEFJ12kXGypNLiNLl72lUFhiOR/EwOOL/JmjM7C+e/4Dc8LD//eEHDBHBhx+PL8cYD8uTK6WuM7OiTMZB6tGNpSI6BhorcERA8BbplLyfr4Ow0WbM5Q3d8tEFmAENjwEvE9mQiLmQFL+NM0V+XauFMoqTjyaS6qyoRCDKfAjgmGKn43y8m+PpnvLXyPrCY+R9SoGrZ4sLhuG1mR6BuFrIqeHllzaMIzqY/AhbQfLD3eHLIwTv47tn/NOe5P/4+cvhHr03EN3Pt0U0f1Eeb1v99S8EQjF6kRrSzCMG6mEQ4myKo/SMgRksUCdqsM/wMZs1kPKLlBjJN9CvWA1Sj1as4OKWJm6ZYf11jIcdKVuO5ZFUI/+JiQNRfWkDe2a2STsNVlQS4AVoB6bIwLTM0EQlqzQj77PVNxJtmZ4zscozWLyJUVYjjwzhG7zbOZjAsQSm//UOrxYDhblZovMTeWaaALxH3Cn5aN3qOLBAo8wzvHsehrjJIfqk7ZwDLsExuMBcHPc4Tn5Qj3Zo/mCyv/1aJMrJ7Pnz7e8Pd2Ty+P0B/xyRye3Tx+fbL4+fK7EwS3E+Okrq0UpTsMcmiumBrJ8evxw+7h8rJCQFEXzCOtJy/3T7cPuH/adH3eaxIg0VD+Mjx93ABhG6TGmtwM9byBOXIFZXeBjjGrVjsvqAKhMTdMsl05jFK88GSp8yDi3OlIkn7IOHSjzKGRxQFpY40MfyPUtOzcQR9cWBCY+6PFidc1S+nFdf1e1QwaIXHoSqEw0VoK9OF0WRPE3XznG019ZgiA6vc3E9vUwwhapeRSZswXto57UR807ZfbUtjIba1gtOLYU7Ewk9kcWmKb4Kw2EgEk3ky37QVks8Yr02RRR7UrxKkWXCYe14jxU9ivNNFMEpKMwSxQlYenFxcMRcoZwyrnYe45F2LGGvTZE8fIHCIrmLy1vuCgbGemSLQu+TNtfVOjJTq7slD/V7LoDahpapL/jl2BK913PWB/p3qau/nW8HMUiFVAjlF1c/IbweHZTjmnerOWxFru5/UXeLGMuJQNxxsIx2F4MFIOx9uprI/HsnQA/jRRoAV9nTxzPM6OAXzKKgLtlhnr/sdqwzpOC4etE7PdUHLgt2ktKRh1VWe0K+go45sBBM1ibDgGmD5cqXf5ajhI8KSQCWhsWjHSh2PsdGyXWdmMlu5D11UULH+hAUHSwK539TTzoms8b6KIiewNC2/NINmA/K3oGr3XyFTd9NoGqJvVcqSoD5MSSc1x4Xj+4dD1BZAWeqZkuZkcTI45JFvpmVZPKEonx0ry8YFPPp5K1+OjeGOKFLPdrB2HrlplkOjvEWb4w3+u98KYh0xD1ns7wFLnzJ83nVmIXF9VVqen34nXfDxNyVH9pWjDeUfAVvEj/I/b2RKfek9ID9cpw0NMH7NvCBLADKj6U0hVqnLYp96Nm79Kuez7ftsxgU//0/D6EWewEoAQA=';

    private int $userId;

    /** @var array<string, int> */
    private array $id = [];

    public function run(): void
    {
        if (! DB::table('companies')->where('id', self::COMPANY_ID)->exists()) {
            throw new RuntimeException('Company 3 must exist before running BoatManufacturingDemoSeeder.');
        }

        $this->userId = (int) DB::table('company_user')
            ->where('company_id', self::COMPANY_ID)
            ->orderBy('user_id')
            ->value('user_id');

        if ($this->userId <= 0) {
            throw new RuntimeException('Company 3 must have at least one user.');
        }

        DB::transaction(function (): void {
            $this->seedFoundation();
            $this->seedEngineering();
            $this->seedCommercial();
            $this->seedInventory();
            $this->seedProduction();
            $this->seedPlanningAndAnalytics();
        });

        $this->assertEveryTenantTableHasData();
    }

    private function seedFoundation(): void
    {
        $this->id['unit_un'] = $this->upsertId('units', ['company_id' => self::COMPANY_ID, 'code' => 'UN'], [
            'name' => 'Unidade', 'is_active' => true,
        ]);
        $this->id['unit_kg'] = $this->upsertId('units', ['company_id' => self::COMPANY_ID, 'code' => 'KG'], [
            'name' => 'Quilograma', 'is_active' => true,
        ]);
        $this->id['unit_m2'] = $this->upsertId('units', ['company_id' => self::COMPANY_ID, 'code' => 'M2'], [
            'name' => 'Metro quadrado', 'is_active' => true,
        ]);

        $this->id['category_boat'] = $this->upsertId('product_categories', ['company_id' => self::COMPANY_ID, 'code' => 'EMBARCACOES'], [
            'name' => 'Embarcações', 'description' => 'Barcos e conjuntos navais acabados', 'is_active' => true,
        ]);
        $this->id['category_material'] = $this->upsertId('product_categories', ['company_id' => self::COMPANY_ID, 'code' => 'MATERIAIS-NAVAIS'], [
            'name' => 'Materiais navais', 'description' => 'Matérias-primas e componentes para construção naval', 'is_active' => true,
        ]);
        $this->id['brand'] = $this->upsertId('product_brands', ['company_id' => self::COMPANY_ID, 'code' => 'OCEANCRAFT'], [
            'name' => 'OceanCraft', 'description' => 'Linha demonstrativa de embarcações', 'is_active' => true,
        ]);

        $this->id['plant'] = $this->upsertId('plants', ['company_id' => self::COMPANY_ID, 'code' => 'ESTALEIRO-SC'], [
            'name' => 'Estaleiro Santa Catarina', 'timezone' => 'America/Sao_Paulo', 'is_active' => true,
        ]);
        $this->id['warehouse_raw'] = $this->upsertId('warehouses', ['company_id' => self::COMPANY_ID, 'code' => 'MP-NAVAL'], [
            'plant_id' => $this->id['plant'], 'name' => 'Matérias-primas navais', 'is_active' => true,
        ]);
        $this->id['warehouse_fg'] = $this->upsertId('warehouses', ['company_id' => self::COMPANY_ID, 'code' => 'PA-BARCOS'], [
            'plant_id' => $this->id['plant'], 'name' => 'Embarcações acabadas', 'is_active' => true,
        ]);

        $this->upsertId('account_invitations', ['company_id' => self::COMPANY_ID, 'email' => 'engenharia.demo@oceancraft.local'], [
            'invited_by_user_id' => $this->userId, 'name' => 'Engenheiro Naval Demo', 'role_slug' => 'company-engineer',
            'token' => hash('sha512', 'boat-demo-company-3-invitation'), 'expires_at' => now()->addDays(7),
            'meta' => $this->json(['scenario' => 'boat-manufacturing-demo']),
        ]);
        $this->upsertId('audit_logs', ['company_id' => self::COMPANY_ID, 'event' => 'demo.boat_manufacturing.seeded'], [
            'user_id' => $this->userId, 'severity' => 'info',
            'context' => $this->json(['industry' => 'boat_manufacturing']), 'occurred_at' => now(),
            'ip_address' => '127.0.0.1', 'user_agent' => 'BoatManufacturingDemoSeeder',
        ]);
    }

    private function seedEngineering(): void
    {
        $products = [
            'boat' => ['FL290XL', 'Florida 290 XL', 'FG', $this->id['unit_un'], true, true, $this->id['category_boat']],
            'hull' => ['HULL-280-WIP', 'Casco laminado 28 pés', 'WIP', $this->id['unit_un'], true, false, $this->id['category_material']],
            'deck' => ['DECK-280-WIP', 'Convés modular 28 pés', 'WIP', $this->id['unit_un'], true, false, $this->id['category_material']],
            'engine' => ['ENGINE-300-HP', 'Motor de popa 300 HP', 'RAW', $this->id['unit_un'], false, true, $this->id['category_material']],
            'resin' => ['RESIN-MARINE-KG', 'Resina poliéster naval', 'RAW', $this->id['unit_kg'], true, false, $this->id['category_material']],
            'fiber' => ['FIBER-600-M2', 'Manta de fibra de vidro 600 g/m²', 'RAW', $this->id['unit_m2'], true, false, $this->id['category_material']],
            'seat' => ['SEAT-NAUTIC', 'Banco náutico estofado', 'RAW', $this->id['unit_un'], false, false, $this->id['category_material']],
            'electric' => ['ELEC-PANEL-12V', 'Painel elétrico marítimo 12 V', 'RAW', $this->id['unit_un'], false, true, $this->id['category_material']],
            'paint' => ['GELCOAT-WHITE-KG', 'Gelcoat naval branco', 'CONSUMABLE', $this->id['unit_kg'], true, false, $this->id['category_material']],
            'reinforcement' => ['TRANSOM-REINF-300', 'Reforço estrutural de espelho de popa', 'RAW', $this->id['unit_un'], false, false, $this->id['category_material']],
            'windshield' => ['WINDSHIELD-280', 'Para-brisa curvo OceanCraft 280', 'RAW', $this->id['unit_un'], false, false, $this->id['category_material']],
        ];

        foreach ($products as $key => [$sku, $description, $type, $unitId, $lot, $serial, $categoryId]) {
            $this->id[$key] = $this->upsertId('products', ['company_id' => self::COMPANY_ID, 'sku' => $sku], [
                'description' => $description, 'product_type' => $type, 'unit_id' => $unitId,
                'category_id' => $categoryId, 'brand_id' => $key === 'boat' ? $this->id['brand'] : null,
                'safety_stock' => in_array($key, ['resin', 'fiber'], true) ? 100 : 0,
                'lead_time_days' => $key === 'engine' ? 30 : 5, 'lot_control' => $lot, 'serial_control' => $serial,
                'is_active' => true, 'lifecycle_status' => 'ACTIVE',
                'technical_attributes' => $this->json(['marine_grade' => true, 'demo' => true]),
            ]);
            $this->upsertId('product_versions', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id[$key], 'version_number' => 1], [
                'status' => 'APPROVED', 'effective_from' => now()->subMonths(3)->toDateString(),
                'compatibility_rule' => 'BACKWARD', 'change_summary' => 'Versão inicial para cenário demonstrativo de barcos',
                'payload' => $this->json(['sku' => $sku, 'description' => $description, 'marine_grade' => true]),
                'created_by' => $this->userId, 'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3),
            ]);
        }

        $centers = [
            'cut' => ['CORTE-CNC', 'Corte CNC e preparação', 'MACHINE', 16],
            'lamination' => ['LAMINACAO', 'Laminação de casco e convés', 'LINE', 16],
            'assembly' => ['MONTAGEM', 'Montagem naval', 'LINE', 16],
            'finish' => ['ACABAMENTO', 'Acabamento e pintura', 'LINE', 16],
            'quality' => ['TESTES', 'Inspeção e testes de água', 'LINE', 8],
        ];
        foreach ($centers as $key => [$code, $name, $type, $capacity]) {
            $this->id['wc_'.$key] = $this->upsertId('work_centers', ['company_id' => self::COMPANY_ID, 'code' => $code], [
                'plant_id' => $this->id['plant'], 'name' => $name, 'resource_type' => $type,
                'capacity_per_day' => $capacity, 'efficiency_factor' => 92, 'is_active' => true,
            ]);
            $this->upsertId('work_center_shifts', ['company_id' => self::COMPANY_ID, 'work_center_id' => $this->id['wc_'.$key], 'name' => 'Turno diurno'], [
                'shift_start' => '07:30:00', 'shift_end' => '16:30:00', 'capacity_hours' => 8, 'is_active' => true,
            ]);
            $this->upsertId('work_center_hour_rates', ['company_id' => self::COMPANY_ID, 'work_center_id' => $this->id['wc_'.$key], 'effective_from' => now()->startOfYear()->toDateString()], [
                'hourly_rate' => 180 + ($capacity * 5), 'currency' => 'BRL', 'status' => 'ACTIVE',
                'approved_by' => $this->userId, 'approved_at' => now()->startOfYear(),
                'change_reason' => 'Custo padrão do cenário de fabricação naval',
            ]);
        }

        $resources = [
            'cnc' => ['CNC-NAVAL-01', 'Router CNC Naval 5 eixos', 'MACHINE', 'cut'],
            'mold' => ['MOLDE-CASCO-280', 'Molde de casco OceanCraft 280', 'TOOL', 'lamination'],
            'line' => ['LINHA-MONT-01', 'Linha de montagem naval 01', 'LINE', 'assembly'],
            'booth' => ['CABINE-PINT-01', 'Cabine de pintura naval', 'EQUIPMENT', 'finish'],
            'tank' => ['TANQUE-TESTE-01', 'Tanque de teste de estanqueidade', 'EQUIPMENT', 'quality'],
        ];
        foreach ($resources as $key => [$code, $name, $type, $center]) {
            $this->id['resource_'.$key] = $this->upsertId('production_resources', ['company_id' => self::COMPANY_ID, 'code' => $code], [
                'plant_id' => $this->id['plant'], 'work_center_id' => $this->id['wc_'.$center],
                'name' => $name, 'resource_type' => $type, 'status' => 'ACTIVE', 'capacity_per_day' => 8,
                'efficiency_factor' => 95, 'effective_from' => now()->subYear()->toDateString(),
                'metadata' => $this->json(['manufacturer' => 'Demo Marine Equipment']),
            ]);
        }

        $this->id['bom'] = $this->upsertId('bom_headers', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id['boat'], 'version_number' => 1], [
            'status' => 'APPROVED', 'effective_from' => now()->subMonths(3)->toDateString(),
            'description' => 'Estrutura padrão da lancha OceanCraft 280 Sport',
            'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3),
        ]);
        $bomComponents = [['hull', 1, 1], ['deck', 2, 1], ['engine', 3, 1], ['seat', 4, 6], ['electric', 5, 1], ['windshield', 6, 1]];
        foreach ($bomComponents as [$component, $line, $quantity]) {
            $this->upsertId('bom_items', ['bom_header_id' => $this->id['bom'], 'line_no' => $line], [
                'company_id' => self::COMPANY_ID, 'component_product_id' => $this->id[$component],
                'unit_id' => DB::table('products')->where('id', $this->id[$component])->value('unit_id'),
                'quantity_per' => $quantity,
            ]);
        }

        $substructures = [
            'bom_hull' => ['hull', 'Subestrutura: casco laminado 28 pés', [
                ['resin', 1, 120], ['fiber', 2, 250], ['paint', 3, 18], ['reinforcement', 4, 1],
            ]],
            'bom_deck' => ['deck', 'Subestrutura: convés modular 28 pés', [
                ['resin', 1, 35], ['fiber', 2, 85], ['electric', 3, 1], ['seat', 4, 6], ['windshield', 5, 1],
            ]],
        ];
        foreach ($substructures as $bomKey => [$product, $description, $components]) {
            $this->id[$bomKey] = $this->upsertId('bom_headers', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id[$product], 'version_number' => 1], [
                'status' => 'APPROVED', 'effective_from' => now()->subMonths(3)->toDateString(),
                'description' => $description, 'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3),
            ]);
            foreach ($components as [$component, $line, $quantity]) {
                $this->upsertId('bom_items', ['bom_header_id' => $this->id[$bomKey], 'line_no' => $line], [
                    'company_id' => self::COMPANY_ID, 'component_product_id' => $this->id[$component],
                    'unit_id' => DB::table('products')->where('id', $this->id[$component])->value('unit_id'),
                    'quantity_per' => $quantity,
                ]);
            }
        }

        $this->seedBoatStructuresFromSource();

        $this->id['routing'] = $this->upsertId('routing_versions', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id['boat'], 'version_number' => 1], [
            'status' => 'APPROVED', 'effective_from' => now()->subMonths(3)->toDateString(),
            'description' => 'Roteiro padrão de construção da OceanCraft 280',
            'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3),
        ]);
        $operations = [
            ['cut', 10, 'CORTE', 'Corte de reforços e painéis', 1, 45, 180, 30, 15, 'cnc'],
            ['lamination', 20, 'LAMINAR', 'Laminação do casco', 2, 120, 960, 240, 30, 'mold'],
            ['assembly', 30, 'MONTAR', 'Montagem estrutural e motorização', 3, 90, 720, 120, 30, 'line'],
            ['finish', 40, 'ACABAR', 'Gelcoat, pintura e acabamento', 4, 60, 480, 120, 20, 'booth'],
            ['quality', 50, 'TESTAR', 'Inspeção final e teste de água', 5, 30, 180, 30, 10, 'tank'],
        ];
        foreach ($operations as [$center, $number, $code, $name, $sequence, $setup, $runtime, $queue, $move, $resource]) {
            $opId = $this->upsertId('routing_operations', ['routing_version_id' => $this->id['routing'], 'operation_no' => $number], [
                'company_id' => self::COMPANY_ID, 'work_center_id' => $this->id['wc_'.$center],
                'operation_code' => $code, 'operation_name' => $name, 'sequence' => $sequence,
                'setup_time_minutes' => $setup, 'runtime_minutes' => $runtime,
                'queue_time_minutes' => $queue, 'move_time_minutes' => $move, 'is_outsourced' => false,
            ]);
            $this->id['routing_op_'.$sequence] = $opId;
            $this->id['std_'.$sequence] = $this->upsertId('routing_operation_standard_times', ['company_id' => self::COMPANY_ID, 'routing_operation_id' => $opId, 'version_number' => 1], [
                'status' => 'APPROVED', 'time_basis' => 'PER_PROCESS', 'setup_scope' => $sequence === 1 ? 'ROUTING' : 'OPERATION',
                'base_quantity' => 1, 'setup_time_minutes' => $setup, 'runtime_minutes' => $runtime,
                'queue_time_minutes' => $queue, 'move_time_minutes' => $move, 'efficiency_factor' => 100,
                'yield_factor' => 100, 'effective_from' => now()->subMonths(3)->toDateString(),
                'created_by' => $this->userId, 'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3),
                'change_reason' => 'Tempos homologados para demonstração naval',
            ]);
            $this->id['op_resource_'.$sequence] = $this->id['resource_'.$resource];
        }

        $hash = hash('sha256', 'boat-demo-routing-v1');
        $this->id['routing_snapshot'] = $this->upsertId('routing_version_snapshots', ['company_id' => self::COMPANY_ID, 'routing_version_id' => $this->id['routing']], [
            'product_id' => $this->id['boat'], 'version_number' => 1, 'status' => 'APPROVED',
            'effective_from' => now()->subMonths(3)->toDateString(), 'description' => 'Snapshot do roteiro naval v1',
            'approved_by' => $this->userId, 'approved_at' => now()->subMonths(3), 'frozen_at' => now()->subMonths(3),
            'snapshot_hash' => $hash, 'created_by' => $this->userId,
        ]);
        foreach ($operations as $operation) {
            [$center, $number, $code, $name, $sequence, $setup, $runtime, $queue, $move] = $operation;
            $this->upsertId('routing_operation_snapshots', ['routing_version_snapshot_id' => $this->id['routing_snapshot'], 'operation_no' => $number], [
                'company_id' => self::COMPANY_ID, 'routing_version_id' => $this->id['routing'],
                'standard_time_id' => $this->id['std_'.$sequence], 'standard_time_version' => 1,
                'work_center_id' => $this->id['wc_'.$center], 'operation_code' => $code,
                'operation_name' => $name, 'sequence' => $sequence, 'setup_time_minutes' => $setup,
                'runtime_minutes' => $runtime, 'queue_time_minutes' => $queue, 'move_time_minutes' => $move,
                'is_outsourced' => false,
            ]);
        }

        $this->id['eco'] = $this->upsertId('engineering_change_orders', ['company_id' => self::COMPANY_ID, 'eco_number' => 'ECO-BOAT-0001'], [
            'title' => 'Reforço do espelho de popa para motor 300 HP',
            'description' => 'Atualização demonstrativa de engenharia para reforço estrutural.',
            'status' => 'IMPLEMENTED', 'effective_from' => now()->subMonth()->toDateString(),
            'requested_by' => $this->userId, 'submitted_by' => $this->userId, 'submitted_at' => now()->subMonths(2),
            'approved_by' => $this->userId, 'approved_at' => now()->subMonths(2)->addDay(),
            'implemented_by' => $this->userId, 'implemented_at' => now()->subMonth(),
            'impact_summary' => $this->json(['open_orders' => 2, 'risk' => 'LOW']),
        ]);
        $this->upsertId('engineering_change_order_lines', ['company_id' => self::COMPANY_ID, 'engineering_change_order_id' => $this->id['eco'], 'target_domain' => 'BOM', 'target_entity_id' => $this->id['bom']], [
            'change_type' => 'VERSION_CHANGE', 'from_version_number' => 1, 'to_version_number' => 1,
            'effective_from' => now()->subMonth()->toDateString(), 'impact_level' => 'MEDIUM',
            'change_summary' => 'Reforço estrutural homologado no conjunto do casco.',
        ]);
    }

    private function seedBoatStructuresFromSource(): void
    {
        $rows = $this->boatSourceRows();

        if ($rows === false || count($rows) < 2) {
            throw new RuntimeException('Boat product structure source is empty or cannot be read.');
        }

        /** @var array<string, array{model: string, process: string, subprocess: string, materials: array<string, array<string, mixed>>}> $structures */
        $structures = [];
        /** @var array<string, int> $materialIds */
        $materialIds = [];

        foreach (array_slice($rows, 1) as $line) {
            [$model, $process, $subprocess, $code, $material, $quantity, $unit, $cmv] = array_pad(explode("\t", $line), 8, '');
            $model = trim($model);
            $process = trim($process);
            $subprocess = trim($subprocess);
            $material = trim($material);

            if ($model === '' || $process === '' || $subprocess === '' || $material === '') {
                continue;
            }

            $unitCode = $this->normalizeSourceUnit($unit);
            $unitId = $this->sourceUnitId($unitCode);
            $materialKey = trim($code).'|'.$material.'|'.$unitCode;
            $materialSku = 'MAT-'.substr(hash('sha1', $materialKey), 0, 14);

            if (! isset($materialIds[$materialKey])) {
                $materialIds[$materialKey] = $this->upsertId('products', ['company_id' => self::COMPANY_ID, 'sku' => $materialSku], [
                    'description' => $material, 'product_type' => 'RAW', 'unit_id' => $unitId,
                    'category_id' => $this->id['category_material'], 'safety_stock' => 0, 'lead_time_days' => 15,
                    'lot_control' => in_array($unitCode, ['KG', 'M2'], true), 'serial_control' => false, 'is_active' => true,
                    'lifecycle_status' => 'ACTIVE',
                    'technical_attributes' => $this->json([
                        'source_model' => $model, 'source_code' => trim($code) ?: null,
                        'source_unit' => $unitCode, 'cmv' => $this->sourceDecimal($cmv),
                    ]),
                ]);
            }

            $structureKey = $model.'|'.$process.'|'.$subprocess;
            $structures[$structureKey] ??= [
                'model' => $model, 'process' => $process, 'subprocess' => $subprocess, 'materials' => [],
            ];
            $structures[$structureKey]['materials'][$materialKey] ??= [
                'product_id' => $materialIds[$materialKey], 'unit_id' => $unitId, 'quantity' => 0.0,
            ];
            $structures[$structureKey]['materials'][$materialKey]['quantity'] += $this->sourceDecimal($quantity);
        }

        /** @var array<string, list<array{product_id: int, process: string, subprocess: string}>> $modelSubassemblies */
        $modelSubassemblies = [];
        foreach ($structures as $structure) {
            $model = $structure['model'];
            $subassemblySku = 'SUB-'.$model.'-'.substr(hash('sha1', $structure['process'].'|'.$structure['subprocess']), 0, 12);
            $subassemblyId = $this->upsertId('products', ['company_id' => self::COMPANY_ID, 'sku' => $subassemblySku], [
                'description' => $model.' — '.$structure['process'].' — '.$structure['subprocess'], 'product_type' => 'WIP',
                'unit_id' => $this->id['unit_un'], 'category_id' => $this->id['category_material'], 'safety_stock' => 0,
                'lead_time_days' => 3, 'lot_control' => false, 'serial_control' => false, 'is_active' => true,
                'lifecycle_status' => 'ACTIVE', 'technical_attributes' => $this->json(['source_model' => $model, 'process' => $structure['process'], 'subprocess' => $structure['subprocess']]),
            ]);
            $subassemblyBomId = $this->upsertId('bom_headers', ['company_id' => self::COMPANY_ID, 'product_id' => $subassemblyId, 'version_number' => 1], [
                'status' => 'APPROVED', 'effective_from' => self::DEMO_DATE,
                'description' => 'Estrutura importada: '.$structure['process'].' / '.$structure['subprocess'],
                'approved_by' => $this->userId, 'approved_at' => now(),
            ]);
            DB::table('bom_items')->where('bom_header_id', $subassemblyBomId)->delete();
            $lineNo = 1;
            foreach ($structure['materials'] as $material) {
                $this->upsertId('bom_items', ['bom_header_id' => $subassemblyBomId, 'line_no' => $lineNo++], [
                    'company_id' => self::COMPANY_ID, 'component_product_id' => $material['product_id'],
                    'unit_id' => $material['unit_id'], 'quantity_per' => round($material['quantity'], 6),
                ]);
            }
            $modelSubassemblies[$model][] = [
                'product_id' => $subassemblyId, 'process' => $structure['process'], 'subprocess' => $structure['subprocess'],
            ];
        }

        foreach ($modelSubassemblies as $model => $subassemblies) {
            $modelId = $this->upsertId('products', ['company_id' => self::COMPANY_ID, 'sku' => $model], [
                'description' => 'Embarcação Florida '.str_replace('FL', '', $model), 'product_type' => 'FG',
                'unit_id' => $this->id['unit_un'], 'category_id' => $this->id['category_boat'], 'brand_id' => $this->id['brand'],
                'safety_stock' => 0, 'lead_time_days' => 30, 'lot_control' => true, 'serial_control' => true,
                'is_active' => true, 'lifecycle_status' => 'ACTIVE', 'technical_attributes' => $this->json(['source_model' => $model]),
            ]);
            $modelBomId = $this->upsertId('bom_headers', ['company_id' => self::COMPANY_ID, 'product_id' => $modelId, 'version_number' => 1], [
                'status' => 'APPROVED', 'effective_from' => self::DEMO_DATE,
                'description' => 'Estrutura principal importada da embarcação '.$model,
                'approved_by' => $this->userId, 'approved_at' => now(),
            ]);
            DB::table('bom_items')->where('bom_header_id', $modelBomId)->delete();
            foreach (array_values($subassemblies) as $index => $subassembly) {
                $this->upsertId('bom_items', ['bom_header_id' => $modelBomId, 'line_no' => $index + 1], [
                    'company_id' => self::COMPANY_ID, 'component_product_id' => $subassembly['product_id'],
                    'unit_id' => $this->id['unit_un'], 'quantity_per' => 1,
                ]);
            }

            if ($model === 'FL290XL') {
                $this->id['boat'] = $modelId;
                $this->id['bom'] = $modelBomId;
            }
        }
    }

    /** @return list<string> */
    private function boatSourceRows(): array
    {
        $sourcePath = getenv('BOAT_BOM_SOURCE') ?: database_path('seeders/data/boat_bom.tsv');

        if (is_readable($sourcePath)) {
            return file($sourcePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        }

        $source = gzdecode(base64_decode(self::BOAT_BOM_SOURCE_GZIP_BASE64, true) ?: '');

        if ($source === false) {
            throw new RuntimeException('Embedded boat BOM source cannot be decoded.');
        }

        return array_values(array_filter(preg_split('/\\R/', $source) ?: [], static fn (string $line): bool => $line !== ''));
    }

    private function normalizeSourceUnit(string $unit): string
    {
        return match (mb_strtoupper(trim($unit))) {
            'KG' => 'KG', 'M2', 'M²' => 'M2', 'M' => 'M', 'MM' => 'MM', 'KIT' => 'KIT', default => 'UN',
        };
    }

    private function sourceUnitId(string $code): int
    {
        return $this->id['source_unit_'.$code] ??= $this->upsertId('units', ['company_id' => self::COMPANY_ID, 'code' => $code], [
            'name' => match ($code) {
                'M' => 'Metro', 'MM' => 'Milímetro', 'KIT' => 'Kit', default => $code
            }, 'is_active' => true,
        ]);
    }

    private function sourceDecimal(string $value): float
    {
        $normalized = str_replace(['.', ','], ['', '.'], trim($value));

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }

    private function seedCommercial(): void
    {
        $this->id['supplier'] = $this->upsertId('suppliers', ['company_id' => self::COMPANY_ID, 'code' => 'SUP-MARINE-01'], [
            'name' => 'Marine Power Brasil', 'person_type' => 'PJ',
            'email' => 'vendas@marinepower.demo', 'phone' => '+55 47 3333-1000', 'status' => 'ACTIVE',
            'default_lead_time_days' => 30, 'payment_terms' => '30/60 dias',
        ]);
        $this->upsertId('supplier_products', ['company_id' => self::COMPANY_ID, 'supplier_id' => $this->id['supplier'], 'product_id' => $this->id['engine']], [
            'supplier_sku' => 'MP-OUTBOARD-300', 'moq' => 1, 'lead_time_days' => 30,
            'unit_price' => 118000, 'is_preferred' => true, 'is_active' => true,
        ]);
        $this->id['supplier_composites'] = $this->upsertId('suppliers', ['company_id' => self::COMPANY_ID, 'code' => 'SUP-COMPOSITES-01'], [
            'name' => 'Compósitos do Atlântico', 'email' => 'comercial@compositos.demo', 'phone' => '+55 48 3333-1100',
            'status' => 'ACTIVE', 'default_lead_time_days' => 12, 'payment_terms' => '28 dias',
        ]);
        $this->id['supplier_outfitting'] = $this->upsertId('suppliers', ['company_id' => self::COMPANY_ID, 'code' => 'SUP-NAUTIC-01'], [
            'name' => 'Náutica Equipamentos Sul', 'email' => 'vendas@nauticaequipamentos.demo', 'phone' => '+55 48 3333-1200',
            'status' => 'ACTIVE', 'default_lead_time_days' => 15, 'payment_terms' => '30 dias',
        ]);
        foreach ([
            ['supplier_composites', 'resin', 'CA-RES-01', 26], ['supplier_composites', 'fiber', 'CA-FIB-600', 19],
            ['supplier_composites', 'paint', 'CA-GEL-W', 42], ['supplier_composites', 'reinforcement', 'CA-REF-300', 1800],
            ['supplier_outfitting', 'seat', 'NES-SEAT-01', 2400], ['supplier_outfitting', 'electric', 'NES-PANEL-12', 3200],
            ['supplier_outfitting', 'windshield', 'NES-WIND-280', 7800],
        ] as [$supplierKey, $product, $supplierSku, $price]) {
            $this->upsertId('supplier_products', ['company_id' => self::COMPANY_ID, 'supplier_id' => $this->id[$supplierKey], 'product_id' => $this->id[$product]], [
                'supplier_sku' => $supplierSku, 'moq' => 1, 'lead_time_days' => $supplierKey === 'supplier_composites' ? 12 : 15,
                'unit_price' => $price, 'is_preferred' => true, 'is_active' => true,
            ]);
        }

        $this->id['requisition'] = $this->upsertId('purchase_requisitions', ['company_id' => self::COMPANY_ID, 'requisition_number' => 'REQ-BOAT-0001'], [
            'status' => 'APPROVED', 'required_date' => now()->addDays(20)->toDateString(), 'source_type' => 'MRP',
            'requested_by' => $this->userId, 'approved_by' => $this->userId, 'approved_at' => now()->subDays(10),
            'notes' => 'Motor para próxima lancha OceanCraft 280.',
        ]);
        $this->id['requisition_line'] = $this->upsertId('purchase_requisition_lines', ['company_id' => self::COMPANY_ID, 'purchase_requisition_id' => $this->id['requisition'], 'product_id' => $this->id['engine']], [
            'warehouse_id' => $this->id['warehouse_raw'], 'supplier_id' => $this->id['supplier'],
            'suggested_quantity' => 1, 'requested_quantity' => 1, 'moq_applied' => 1, 'lead_time_days' => 30,
            'need_by_date' => now()->addDays(20)->toDateString(), 'order_date' => now()->subDays(10)->toDateString(),
            'status' => 'CONVERTED', 'source_requirement_key' => 'DEMO-BOAT-ENGINE-REQ',
            'mrp_reference_date' => now()->subDays(10)->toDateString(),
        ]);
        $this->id['quotation'] = $this->upsertId('purchase_quotations', ['company_id' => self::COMPANY_ID, 'quotation_number' => 'COT-BOAT-0001'], [
            'purchase_requisition_id' => $this->id['requisition'], 'supplier_id' => $this->id['supplier'],
            'quotation_date' => now()->subDays(9)->toDateString(), 'valid_until' => now()->addDays(6)->toDateString(),
            'status' => 'APPROVED', 'received_by' => $this->userId, 'received_at' => now()->subDays(8),
            'approved_by' => $this->userId, 'approved_at' => now()->subDays(7), 'amount_cents' => 11800000,
        ]);
        $this->upsertId('purchase_quotation_lines', ['company_id' => self::COMPANY_ID, 'purchase_quotation_id' => $this->id['quotation'], 'product_id' => $this->id['engine']], [
            'purchase_requisition_line_id' => $this->id['requisition_line'], 'quantity' => 1, 'unit_price' => 118000,
            'notes' => 'Motor, comando eletrônico e hélice inclusos.',
        ]);
        $this->id['purchase_order'] = $this->upsertId('purchase_orders', ['company_id' => self::COMPANY_ID, 'purchase_order_number' => 'PC-BOAT-0001'], [
            'supplier_id' => $this->id['supplier'], 'purchase_requisition_id' => $this->id['requisition'],
            'status' => 'APPROVED', 'order_date' => now()->subDays(7)->toDateString(),
            'expected_delivery_date' => now()->addDays(20)->toDateString(), 'created_by' => $this->userId,
            'approved_by' => $this->userId, 'approved_at' => now()->subDays(7), 'notes' => 'Compra do conjunto propulsor.',
        ]);
        $this->id['purchase_order_line'] = $this->upsertId('purchase_order_lines', ['company_id' => self::COMPANY_ID, 'purchase_order_id' => $this->id['purchase_order'], 'product_id' => $this->id['engine']], [
            'purchase_requisition_line_id' => $this->id['requisition_line'], 'warehouse_id' => $this->id['warehouse_raw'],
            'quantity_ordered' => 1, 'quantity_received' => 1, 'unit_price' => 118000,
            'need_by_date' => now()->addDays(20)->toDateString(), 'promised_date' => now()->subDays(2)->toDateString(),
            'status' => 'RECEIVED',
        ]);
        $this->id['receipt'] = $this->upsertId('purchase_receipts', ['company_id' => self::COMPANY_ID, 'receipt_number' => 'REC-BOAT-0001'], [
            'purchase_order_id' => $this->id['purchase_order'], 'supplier_id' => $this->id['supplier'],
            'receipt_date' => now()->subDays(2)->toDateString(), 'status' => 'POSTED',
            'posted_by' => $this->userId, 'posted_at' => now()->subDays(2), 'notes' => 'Motor recebido e inspecionado.',
        ]);

        foreach ([
            ['REQ-BOAT-0002', 'PC-BOAT-0002', 'supplier_composites', [['resin', 500, 26], ['fiber', 900, 19], ['paint', 120, 42], ['reinforcement', 3, 1800]]],
            ['REQ-BOAT-0003', 'PC-BOAT-0003', 'supplier_outfitting', [['seat', 24, 2400], ['electric', 4, 3200], ['windshield', 2, 7800]]],
        ] as [$requisitionNumber, $purchaseOrderNumber, $supplierKey, $lines]) {
            $requisitionId = $this->upsertId('purchase_requisitions', ['company_id' => self::COMPANY_ID, 'requisition_number' => $requisitionNumber], [
                'status' => 'APPROVED', 'required_date' => now()->addDays(20)->toDateString(), 'source_type' => 'PRODUCTION',
                'requested_by' => $this->userId, 'approved_by' => $this->userId, 'approved_at' => now()->subDays(10),
                'notes' => 'Reposição de componentes para fabricação das lanchas OceanCraft 280.',
            ]);
            $purchaseOrderId = $this->upsertId('purchase_orders', ['company_id' => self::COMPANY_ID, 'purchase_order_number' => $purchaseOrderNumber], [
                'supplier_id' => $this->id[$supplierKey], 'purchase_requisition_id' => $requisitionId, 'status' => 'APPROVED',
                'order_date' => now()->subDays(10)->toDateString(), 'expected_delivery_date' => now()->addDays(10)->toDateString(),
                'created_by' => $this->userId, 'approved_by' => $this->userId, 'approved_at' => now()->subDays(9),
                'notes' => 'Compra demonstrativa de componentes da estrutura multinível.',
            ]);
            foreach ($lines as [$product, $quantity, $price]) {
                $lineId = $this->upsertId('purchase_requisition_lines', ['company_id' => self::COMPANY_ID, 'purchase_requisition_id' => $requisitionId, 'product_id' => $this->id[$product]], [
                    'warehouse_id' => $this->id['warehouse_raw'], 'supplier_id' => $this->id[$supplierKey],
                    'suggested_quantity' => $quantity, 'requested_quantity' => $quantity, 'moq_applied' => 1, 'lead_time_days' => 15,
                    'need_by_date' => now()->addDays(20)->toDateString(), 'order_date' => now()->subDays(10)->toDateString(), 'status' => 'CONVERTED',
                ]);
                $this->upsertId('purchase_order_lines', ['company_id' => self::COMPANY_ID, 'purchase_order_id' => $purchaseOrderId, 'product_id' => $this->id[$product]], [
                    'purchase_requisition_line_id' => $lineId, 'warehouse_id' => $this->id['warehouse_raw'],
                    'quantity_ordered' => $quantity, 'quantity_received' => 0, 'unit_price' => $price,
                    'need_by_date' => now()->addDays(20)->toDateString(), 'promised_date' => now()->addDays(10)->toDateString(), 'status' => 'OPEN',
                ]);
            }
        }

        $this->id['customer'] = $this->upsertId('customers', ['company_id' => self::COMPANY_ID, 'code' => 'CLI-MARINA-01'], [
            'name' => 'Marina Costa Azul', 'person_type' => 'PJ',
            'email' => 'compras@marinacostaazul.demo', 'phone' => '+55 48 3222-2000', 'status' => 'ACTIVE',
            'metadata' => $this->json(['segment' => 'marina', 'city' => 'Florianópolis']),
        ]);
        $this->id['sale'] = $this->upsertId('sales', ['company_id' => self::COMPANY_ID, 'customer_id' => $this->id['customer'], 'notes' => 'Lancha personalizada com kit de navegação costeira.'], [
            'sale_date' => now()->subDays(45)->toDateString(),
            'status' => 'CONFIRMED', 'confirmed_by' => $this->userId, 'confirmed_at' => now()->subDays(44),
            'operational_status' => 'DELIVERED', 'picking_by' => $this->userId, 'picking_at' => now()->subDays(5),
            'invoiced_by' => $this->userId, 'invoiced_at' => now()->subDays(4), 'shipped_by' => $this->userId,
            'shipped_at' => now()->subDays(3), 'delivered_by' => $this->userId, 'delivered_at' => now()->subDays(2),
            'subtotal_cents' => 68500000, 'discount_cents' => 1500000, 'amount_cents' => 67000000,
            'notes' => 'Lancha personalizada com kit de navegação costeira.',
        ]);
        $this->upsertId('sale_lines', ['company_id' => self::COMPANY_ID, 'sale_id' => $this->id['sale'], 'product_id' => $this->id['boat']], [
            'quantity' => 1, 'unit_price' => 670000, 'metadata' => $this->json(['color' => 'branco e azul']),
        ]);
        $this->id['components_sale'] = $this->upsertId('sales', ['company_id' => self::COMPANY_ID, 'customer_id' => $this->id['customer'], 'notes' => 'Venda demonstrativa de componentes náuticos para manutenção.'], [
            'sale_date' => now()->subDays(12)->toDateString(), 'status' => 'CONFIRMED', 'confirmed_by' => $this->userId,
            'confirmed_at' => now()->subDays(12), 'operational_status' => 'DELIVERED', 'picking_by' => $this->userId,
            'picking_at' => now()->subDays(11), 'invoiced_by' => $this->userId, 'invoiced_at' => now()->subDays(11),
            'shipped_by' => $this->userId, 'shipped_at' => now()->subDays(10), 'delivered_by' => $this->userId,
            'delivered_at' => now()->subDays(10), 'subtotal_cents' => 40277000, 'discount_cents' => 0, 'amount_cents' => 40277000,
            'notes' => 'Venda demonstrativa de componentes náuticos para manutenção.',
        ]);
        foreach ([['hull', 1, 140000], ['deck', 1, 90000], ['engine', 1, 145000], ['resin', 20, 36], ['fiber', 30, 28], ['paint', 5, 62], ['reinforcement', 1, 2700], ['seat', 2, 3600], ['electric', 1, 4800], ['windshield', 1, 11200]] as [$product, $quantity, $price]) {
            $this->upsertId('sale_lines', ['company_id' => self::COMPANY_ID, 'sale_id' => $this->id['components_sale'], 'product_id' => $this->id[$product]], [
                'quantity' => $quantity, 'unit_price' => $price, 'metadata' => $this->json(['purpose' => 'manutenção náutica']),
            ]);
        }
    }

    private function seedInventory(): void
    {
        $this->id['receipt_movement'] = $this->upsertId('stock_ledger_movements', ['company_id' => self::COMPANY_ID, 'reference_type' => 'boat_demo_initial_stock', 'reference_id' => 1], [
            'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['resin'], 'movement_type' => 'RECEIPT',
            'target_bucket' => 'AVAILABLE', 'quantity' => 500, 'allocation_strategy' => 'FIFO', 'lot_number' => 'RES-2026-0801',
            'reference_type' => 'boat_demo_initial_stock', 'reference_id' => 1, 'notes' => 'Estoque inicial de resina naval.',
            'movement_at' => now()->subDays(30), 'created_by' => $this->userId,
        ]);
        $this->id['engine_receipt_movement'] = $this->upsertId('stock_ledger_movements', ['company_id' => self::COMPANY_ID, 'reference_type' => 'purchase_receipt', 'reference_id' => $this->id['receipt']], [
            'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['engine'], 'movement_type' => 'RECEIPT',
            'target_bucket' => 'AVAILABLE', 'quantity' => 1, 'allocation_strategy' => 'SPECIFIC',
            'reference_type' => 'purchase_receipt', 'reference_id' => $this->id['receipt'], 'notes' => 'Recebimento do motor 300 HP.',
            'movement_at' => now()->subDays(2), 'created_by' => $this->userId,
        ]);
        foreach ([['fiber', 830], ['seat', 24], ['electric', 4], ['paint', 90], ['reinforcement', 3], ['windshield', 2], ['hull', 2], ['deck', 2]] as [$product, $quantity]) {
            $this->upsertId('stock_ledger_movements', ['company_id' => self::COMPANY_ID, 'reference_type' => 'boat_demo_initial_stock', 'reference_id' => $this->id[$product]], [
                'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id[$product], 'movement_type' => 'RECEIPT',
                'target_bucket' => 'AVAILABLE', 'quantity' => $quantity, 'allocation_strategy' => 'FIFO',
                'reference_type' => 'boat_demo_initial_stock', 'reference_id' => $this->id[$product],
                'notes' => 'Estoque inicial para produção de embarcações.', 'movement_at' => now()->subDays(30), 'created_by' => $this->userId,
            ]);
        }
        $this->upsertId('purchase_receipt_lines', ['company_id' => self::COMPANY_ID, 'purchase_receipt_id' => $this->id['receipt'], 'product_id' => $this->id['engine']], [
            'purchase_order_line_id' => $this->id['purchase_order_line'], 'warehouse_id' => $this->id['warehouse_raw'],
            'quantity_received' => 1, 'stock_ledger_movement_id' => $this->id['engine_receipt_movement'], 'notes' => 'Serial conferido no recebimento.',
        ]);
        foreach ([['resin', 420, 20], ['fiber', 800, 50], ['engine', 1, 0], ['seat', 24, 6], ['electric', 4, 1], ['paint', 90, 5], ['reinforcement', 3, 0], ['windshield', 2, 0], ['hull', 2, 0], ['deck', 2, 0], ['boat', 1, 0]] as [$product, $available, $reserved]) {
            $warehouse = $product === 'boat' ? $this->id['warehouse_fg'] : $this->id['warehouse_raw'];
            $this->upsertId('inventory_balances', ['company_id' => self::COMPANY_ID, 'warehouse_id' => $warehouse, 'product_id' => $this->id[$product]], [
                'qty_available' => $available, 'qty_reserved' => $reserved, 'qty_in_transit' => 0, 'qty_inspection' => 0,
                'last_movement_at' => now()->subDays(2),
            ]);
        }
        $this->id['resin_lot'] = $this->upsertId('inventory_lots', ['company_id' => self::COMPANY_ID, 'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['resin'], 'lot_number' => 'RES-2026-0801'], [
            'manufactured_at' => now()->subMonths(2)->toDateString(), 'expires_at' => now()->addYear()->toDateString(),
            'status' => 'ACTIVE', 'source_movement_id' => $this->id['receipt_movement'], 'metadata' => $this->json(['certificate' => 'CERT-RES-0801']),
        ]);
        $this->upsertId('inventory_serials', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id['engine'], 'serial_number' => 'ENG300-DEMO-0001'], [
            'warehouse_id' => $this->id['warehouse_raw'], 'status' => 'ACTIVE', 'source_movement_id' => $this->id['engine_receipt_movement'],
            'metadata' => $this->json(['warranty_months' => 36]),
        ]);
        $this->upsertId('inventory_reservations', ['company_id' => self::COMPANY_ID, 'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['engine'], 'reference_type' => 'sale', 'reference_id' => $this->id['sale']], [
            'reservation_origin' => 'SALES_ORDER', 'priority' => 10, 'quantity' => 1, 'status' => 'RESERVED',
            'reserved_at' => now()->subDays(5), 'expires_at' => now()->addDays(10), 'created_by' => $this->userId,
        ]);
    }

    private function seedProduction(): void
    {
        $this->id['order'] = $this->upsertId('production_orders', ['company_id' => self::COMPANY_ID, 'order_number' => 'OP-BOAT-0001'], [
            'product_id' => $this->id['boat'], 'warehouse_id' => $this->id['warehouse_fg'],
            'bom_header_id' => $this->id['bom'], 'bom_version_number' => 1, 'routing_version_id' => $this->id['routing'],
            'routing_version_number' => 1, 'source_type' => 'MRP', 'source_reference_type' => 'boat_demo_demand',
            'source_reference_id' => $this->id['sale'], 'status' => 'COMPLETED', 'quantity_planned' => 1,
            'quantity_produced' => 1, 'quantity_scrapped' => 0, 'scheduled_start_date' => now()->subDays(20)->toDateString(),
            'scheduled_end_date' => now()->subDays(3)->toDateString(), 'released_at' => now()->subDays(21),
            'started_at' => now()->subDays(20), 'completed_at' => now()->subDays(3),
            'created_by' => $this->userId, 'released_by' => $this->userId, 'completed_by' => $this->userId,
            'metadata' => $this->json(['customer' => 'Marina Costa Azul', 'model' => 'OceanCraft 280 Sport']),
        ]);
        $hash = hash('sha256', 'boat-demo-order-0001');
        $this->id['bom_snapshot'] = $this->upsertId('production_order_bom_snapshots', ['company_id' => self::COMPANY_ID, 'production_order_id' => $this->id['order']], [
            'product_id' => $this->id['boat'], 'production_order_quantity' => 1, 'reference_date' => now()->subDays(21)->toDateString(),
            'source_bom_header_id' => $this->id['bom'], 'source_bom_version_number' => 1, 'snapshot_hash' => $hash,
            'has_cycle' => false, 'frozen_at' => now()->subDays(21), 'created_by' => $this->userId,
        ]);
        $bomComponents = [['hull', 1, 1], ['deck', 2, 1], ['engine', 3, 1], ['seat', 4, 6], ['electric', 5, 1], ['windshield', 6, 1]];
        foreach ($bomComponents as [$component, $line, $quantity]) {
            $this->upsertId('production_order_bom_item_snapshots', ['production_order_bom_snapshot_id' => $this->id['bom_snapshot'], 'level' => 1, 'parent_product_id' => $this->id['boat'], 'component_product_id' => $this->id[$component], 'line_no' => $line], [
                'company_id' => self::COMPANY_ID, 'source_bom_header_id' => $this->id['bom'], 'source_bom_version_number' => 1,
                'quantity_per' => $quantity, 'quantity_required' => $quantity,
                'quantity_accumulated' => $quantity, 'path' => $this->id['boat'].'>'.$this->id[$component], 'is_cycle' => false,
            ]);
        }
        $this->id['order_snapshot'] = $this->upsertId('production_order_snapshots', ['company_id' => self::COMPANY_ID, 'production_order_id' => $this->id['order']], [
            'product_id' => $this->id['boat'], 'bom_snapshot_id' => $this->id['bom_snapshot'], 'bom_header_id' => $this->id['bom'],
            'bom_version_number' => 1, 'routing_version_snapshot_id' => $this->id['routing_snapshot'],
            'routing_version_id' => $this->id['routing'], 'routing_version_number' => 1, 'quantity_planned' => 1,
            'quantity_scrapped_target' => 0, 'snapshot_hash' => $hash, 'frozen_at' => now()->subDays(21), 'frozen_by' => $this->userId,
        ]);

        $operationData = [
            ['cut', 10, 'CORTE', 'Corte de reforços e painéis', 1, 45, 180, 30, 15],
            ['lamination', 20, 'LAMINAR', 'Laminação do casco', 2, 120, 960, 240, 30],
            ['assembly', 30, 'MONTAR', 'Montagem estrutural e motorização', 3, 90, 720, 120, 30],
            ['finish', 40, 'ACABAR', 'Gelcoat, pintura e acabamento', 4, 60, 480, 120, 20],
            ['quality', 50, 'TESTAR', 'Inspeção final e teste de água', 5, 30, 180, 30, 10],
        ];
        foreach ($operationData as [$center, $number, $code, $name, $sequence, $setup, $runtime, $queue, $move]) {
            $snapshotId = $this->upsertId('production_order_routing_operation_snapshots', ['production_order_snapshot_id' => $this->id['order_snapshot'], 'sequence' => $sequence], [
                'company_id' => self::COMPANY_ID, 'routing_version_id' => $this->id['routing'],
                'standard_time_id' => $this->id['std_'.$sequence], 'standard_time_version' => 1,
                'work_center_id' => $this->id['wc_'.$center], 'operation_no' => $number, 'operation_code' => $code,
                'operation_name' => $name, 'setup_time_minutes' => $setup, 'runtime_minutes' => $runtime,
                'queue_time_minutes' => $queue, 'move_time_minutes' => $move, 'is_outsourced' => false,
            ]);
            $start = now()->subDays(20)->addDays(($sequence - 1) * 3);
            $end = $start->copy()->addMinutes($setup + $runtime + $queue + $move);
            $opId = $this->upsertId('production_order_operations', ['company_id' => self::COMPANY_ID, 'production_order_id' => $this->id['order'], 'sequence' => $sequence], [
                'production_order_routing_operation_snapshot_id' => $snapshotId, 'routing_operation_id' => $this->id['routing_op_'.$sequence],
                'standard_time_id' => $this->id['std_'.$sequence], 'standard_time_version' => 1,
                'operation_no' => $number, 'operation_code' => $code, 'operation_name' => $name,
                'work_center_id' => $this->id['wc_'.$center], 'production_resource_id' => $this->id['op_resource_'.$sequence],
                'actual_production_resource_id' => $this->id['op_resource_'.$sequence], 'operator_id' => $this->userId,
                'status' => 'COMPLETED', 'quantity_planned' => 1, 'setup_scope' => $sequence === 1 ? 'ROUTING' : 'OPERATION',
                'setup_time_minutes' => $setup, 'runtime_time_minutes' => $runtime, 'queue_time_minutes' => $queue,
                'move_time_minutes' => $move, 'productive_time_minutes' => $setup + $runtime,
                'lead_time_minutes' => $queue + $move, 'total_time_minutes' => $setup + $runtime + $queue + $move,
                'planned_start_at' => $start, 'planned_end_at' => $end, 'quantity_processed' => 1,
                'quantity_good' => 1, 'quantity_scrapped' => $sequence === 4 ? 0.02 : 0,
                'quantity_rework' => $sequence === 4 ? 0.02 : 0, 'actual_productive_minutes' => $setup + $runtime + ($sequence * 8),
                'actual_pause_minutes' => 15, 'actual_started_at' => $start, 'actual_completed_at' => $end->copy()->addMinutes(15),
                'calculation_metadata' => $this->json(['source' => 'boat-demo']),
            ]);
            $this->id['order_op_'.$sequence] = $opId;
            foreach ([['START', 0], ['PAUSE', 60], ['RESUME', 75], ['COMPLETE', $runtime]] as [$event, $minutes]) {
                $this->upsertId('production_operation_events', ['company_id' => self::COMPANY_ID, 'idempotency_key' => 'BOAT-DEMO-'.$opId.'-'.$event], [
                    'production_order_operation_id' => $opId, 'event_type' => $event, 'occurred_at' => $start->copy()->addMinutes($minutes),
                    'operator_id' => $this->userId, 'production_resource_id' => $this->id['op_resource_'.$sequence],
                    'reason_code' => $event === 'PAUSE' ? 'INTERVALO' : null, 'notes' => 'Evento demonstrativo de execução naval.',
                ]);
            }
            $outputKeys = [
                'company_id' => self::COMPANY_ID,
                'production_order_operation_id' => $opId,
                'notes' => 'Apontamento aprovado do cenário demonstrativo.',
            ];
            $this->removeDuplicateDemoRows('production_operation_outputs', $outputKeys);
            $this->upsertId('production_operation_outputs', $outputKeys, [
                'reported_at' => $end,
                'production_order_id' => $this->id['order'], 'work_center_id' => $this->id['wc_'.$center],
                'setup_time_minutes' => $setup, 'process_time_minutes' => $runtime,
                'quantity_good' => 1, 'quantity_scrapped' => $sequence === 4 ? 0.02 : 0,
                'quantity_rework' => $sequence === 4 ? 0.02 : 0, 'lot_number' => 'BOAT-280-2026-001',
                'inspection_status' => 'APPROVED', 'scrap_cause_code' => $sequence === 4 ? 'GELCOAT-BOLHA' : null,
                'destination' => $sequence === 5 ? 'FINISHED_GOODS' : 'NEXT_OPERATION',
                'inspected_at' => $end, 'inspection_notes' => 'Inspeção da operação aprovada.',
                'operator_id' => $this->userId, 'created_by' => $this->userId,
                'production_resource_id' => $this->id['op_resource_'.$sequence],
                'notes' => 'Apontamento aprovado do cenário demonstrativo.',
            ]);
        }

        $this->id['issue_movement'] = $this->upsertId('stock_ledger_movements', ['company_id' => self::COMPANY_ID, 'reference_type' => 'production_order', 'reference_id' => $this->id['order']], [
            'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['resin'], 'movement_type' => 'ISSUE',
            'source_bucket' => 'AVAILABLE', 'quantity' => 80, 'allocation_strategy' => 'FIFO', 'lot_number' => 'RES-2026-0801',
            'reference_type' => 'production_order', 'reference_id' => $this->id['order'], 'notes' => 'Consumo de resina na laminação.',
            'movement_at' => now()->subDays(17), 'created_by' => $this->userId,
        ]);
        $this->upsertId('stock_ledger_allocations', ['issue_movement_id' => $this->id['issue_movement'], 'receipt_movement_id' => $this->id['receipt_movement'], 'sequence_no' => 1], [
            'company_id' => self::COMPANY_ID, 'quantity' => 80,
        ]);
        $this->id['consumption'] = $this->upsertId('production_order_material_consumptions', ['company_id' => self::COMPANY_ID, 'idempotency_key' => 'BOAT-DEMO-CONS-RESIN'], [
            'production_order_id' => $this->id['order'], 'production_order_operation_id' => $this->id['order_op_2'],
            'product_id' => $this->id['resin'], 'warehouse_id' => $this->id['warehouse_raw'], 'lot_number' => 'RES-2026-0801',
            'quantity_consumed' => 80, 'quantity_scrapped' => 1.5, 'ledger_movement_id' => $this->id['issue_movement'],
            'reference_bom_component_id' => (string) $this->id['bom'], 'consumed_at' => now()->subDays(17),
            'operator_id' => $this->userId, 'notes' => 'Consumo real da laminação do casco.',
        ]);
        $this->id['reversal_movement'] = $this->upsertId('stock_ledger_movements', ['company_id' => self::COMPANY_ID, 'reference_type' => 'material_consumption_reversal', 'reference_id' => $this->id['consumption']], [
            'warehouse_id' => $this->id['warehouse_raw'], 'product_id' => $this->id['resin'], 'movement_type' => 'RECEIPT',
            'target_bucket' => 'AVAILABLE', 'quantity' => 2, 'lot_number' => 'RES-2026-0801',
            'reference_type' => 'material_consumption_reversal', 'reference_id' => $this->id['consumption'],
            'notes' => 'Estorno de sobra de resina não utilizada.', 'movement_at' => now()->subDays(16), 'created_by' => $this->userId,
        ]);
        DB::table('production_order_material_consumptions')->where('id', $this->id['consumption'])->update(['reversed_by_movement_id' => $this->id['reversal_movement']]);
        $this->upsertId('production_material_consumption_reversals', ['company_id' => self::COMPANY_ID, 'production_order_material_consumption_id' => $this->id['consumption']], [
            'original_ledger_movement_id' => $this->id['issue_movement'], 'reversal_ledger_movement_id' => $this->id['reversal_movement'],
            'quantity' => 2, 'reason' => 'Sobra de material retornada ao estoque', 'created_by' => $this->userId,
        ]);

        $this->id['quality'] = $this->upsertId('production_quality_records', ['company_id' => self::COMPANY_ID, 'production_order_operation_id' => $this->id['order_op_4'], 'record_type' => 'NON_CONFORMITY'], [
            'status' => 'CLOSED', 'quantity' => 0.02, 'cause_code' => 'GELCOAT-BOLHA', 'destination' => 'REWORK',
            'operator_id' => $this->userId, 'production_resource_id' => $this->id['resource_booth'],
            'notes' => 'Pequena bolha corrigida antes da inspeção final.',
        ]);
        $this->upsertId('production_rework_orders', ['company_id' => self::COMPANY_ID, 'source_production_order_operation_id' => $this->id['order_op_4']], [
            'rework_production_order_operation_id' => $this->id['order_op_4'], 'quantity' => 0.02, 'status' => 'COMPLETED',
            'reason_code' => 'GELCOAT-BOLHA', 'notes' => 'Lixamento e reaplicação localizada de gelcoat.',
            'created_by' => $this->userId, 'completed_at' => now()->subDays(5),
        ]);

        $this->id['finished_lot'] = $this->upsertId('inventory_lots', ['company_id' => self::COMPANY_ID, 'warehouse_id' => $this->id['warehouse_fg'], 'product_id' => $this->id['boat'], 'lot_number' => 'BOAT-280-2026-001'], [
            'manufactured_at' => now()->subDays(3)->toDateString(), 'status' => 'ACTIVE', 'source_movement_id' => null,
            'metadata' => $this->json(['production_order' => 'OP-BOAT-0001']),
        ]);
        $this->upsertId('inventory_serials', ['company_id' => self::COMPANY_ID, 'product_id' => $this->id['boat'], 'serial_number' => 'HIN-BR-OCC2800001'], [
            'warehouse_id' => $this->id['warehouse_fg'], 'inventory_lot_id' => $this->id['finished_lot'], 'status' => 'SHIPPED',
            'metadata' => $this->json(['hin' => 'BR-OCC2800001', 'customer' => 'Marina Costa Azul']),
        ]);
        $materialNode = $this->upsertId('genealogy_nodes', ['company_id' => self::COMPANY_ID, 'node_type' => 'LOT', 'source_id' => $this->id['resin_lot']], [
            'source_reference' => 'RES-2026-0801', 'product_id' => $this->id['resin'], 'warehouse_id' => $this->id['warehouse_raw'],
        ]);
        $boatNode = $this->upsertId('genealogy_nodes', ['company_id' => self::COMPANY_ID, 'node_type' => 'PRODUCTION_ORDER', 'source_id' => $this->id['order']], [
            'source_reference' => 'OP-BOAT-0001', 'product_id' => $this->id['boat'], 'warehouse_id' => $this->id['warehouse_fg'],
        ]);
        $this->upsertId('genealogy_relations', ['company_id' => self::COMPANY_ID, 'parent_node_id' => $materialNode, 'child_node_id' => $boatNode, 'relation_type' => 'CONSUMES'], [
            'quantity' => 78, 'uom' => 'KG', 'production_order_id' => $this->id['order'], 'stock_movement_id' => $this->id['issue_movement'],
        ]);

        $this->seedProductionOrdersInEveryStatus();
    }

    private function seedProductionOrdersInEveryStatus(): void
    {
        $orders = [
            ['DRAFT', 'OP-BOAT-DRAFT-0001', 1, 0, null, null, null],
            ['RELEASED', 'OP-BOAT-RELEASED-0001', 1, 0, now()->subDay(), null, null],
            ['IN_PROGRESS', 'OP-BOAT-INPROGRESS-0001', 1, 0.25, now()->subDays(4), now()->subDays(3), null],
            ['PARTIALLY_COMPLETED', 'OP-BOAT-PARTIAL-0001', 2, 1, now()->subDays(12), now()->subDays(11), null],
            ['CANCELLED', 'OP-BOAT-CANCELLED-0001', 1, 0, null, null, null],
        ];

        foreach ($orders as [$status, $number, $planned, $produced, $releasedAt, $startedAt, $completedAt]) {
            $this->upsertId('production_orders', ['company_id' => self::COMPANY_ID, 'order_number' => $number], [
                'product_id' => $this->id['boat'], 'warehouse_id' => $this->id['warehouse_fg'],
                'bom_header_id' => $this->id['bom'], 'bom_version_number' => 1, 'routing_version_id' => $this->id['routing'],
                'routing_version_number' => 1, 'source_type' => 'MANUAL', 'source_reference_type' => 'boat_demo_status',
                'status' => $status, 'quantity_planned' => $planned, 'quantity_produced' => $produced, 'quantity_scrapped' => 0,
                'scheduled_start_date' => now()->addDays(3)->toDateString(), 'scheduled_end_date' => now()->addDays(18)->toDateString(),
                'released_at' => $releasedAt, 'started_at' => $startedAt, 'completed_at' => $completedAt,
                'created_by' => $this->userId, 'released_by' => $releasedAt ? $this->userId : null,
                'completed_by' => $completedAt ? $this->userId : null,
                'metadata' => $this->json(['scenario' => 'boat-demo', 'status_example' => $status]),
            ]);
        }
    }

    private function seedPlanningAndAnalytics(): void
    {
        foreach (range(0, 13) as $offset) {
            foreach (['wc_cut', 'wc_lamination', 'wc_assembly', 'wc_finish', 'wc_quality'] as $centerKey) {
                $date = Carbon::parse(self::DEMO_DATE)->addDays($offset);
                $this->upsertId('production_calendar_days', ['company_id' => self::COMPANY_ID, 'work_center_id' => $this->id[$centerKey], 'calendar_date' => $date->toDateString()], [
                    'is_working_day' => ! $date->isWeekend(), 'available_capacity' => $date->isWeekend() ? 0 : 8,
                    'notes' => $date->isWeekend() ? 'Fim de semana' : 'Calendário padrão do estaleiro',
                ]);
            }
        }
        $this->id['mrp_run'] = $this->upsertId('mrp_plan_runs', ['company_id' => self::COMPANY_ID, 'run_key' => 'BOAT-DEMO-MRP-001'], [
            'status' => 'COMPLETED', 'reference_date' => now()->toDateString(), 'planning_bucket' => 'daily',
            'priority_rule' => 'priority_due_date', 'request_payload' => $this->json(['demand' => [['sku' => 'BOAT-280-SPORT', 'quantity' => 2]]]),
            'result_summary' => $this->json(['purchase_count' => 1, 'production_count' => 1]), 'created_by' => $this->userId,
        ]);
        $purchaseSuggestion = $this->upsertId('mrp_suggestions', ['company_id' => self::COMPANY_ID, 'suggestion_key' => 'BOAT-DEMO-MRP-ENGINE'], [
            'mrp_plan_run_id' => $this->id['mrp_run'], 'suggestion_type' => 'PURCHASE', 'status' => 'CONVERTED',
            'product_id' => $this->id['engine'], 'warehouse_id' => $this->id['warehouse_raw'], 'original_quantity' => 1,
            'approved_quantity' => 1, 'need_by_date' => now()->addDays(20)->toDateString(),
            'release_date' => now()->subDays(10)->toDateString(), 'priority' => 10,
            'source_requirement_key' => 'DEMO-BOAT-ENGINE-REQ', 'purchase_requisition_id' => $this->id['requisition'],
            'decision_reason' => 'Motor necessário para atender carteira de barcos.', 'decided_by' => $this->userId,
            'decided_at' => now()->subDays(10), 'converted_at' => now()->subDays(10),
            'original_payload' => $this->json(['net_requirement' => 1]),
        ]);
        $productionSuggestion = $this->upsertId('mrp_suggestions', ['company_id' => self::COMPANY_ID, 'suggestion_key' => 'BOAT-DEMO-MRP-BOAT'], [
            'mrp_plan_run_id' => $this->id['mrp_run'], 'suggestion_type' => 'PRODUCTION', 'status' => 'CONVERTED',
            'product_id' => $this->id['boat'], 'warehouse_id' => $this->id['warehouse_fg'], 'original_quantity' => 1,
            'approved_quantity' => 1, 'need_by_date' => now()->addDays(30)->toDateString(), 'release_date' => now()->toDateString(),
            'priority' => 20, 'bom_version_number' => 1, 'routing_version_id' => $this->id['routing'],
            'production_order_id' => $this->id['order'], 'decision_reason' => 'Demanda confirmada da Marina Costa Azul.',
            'decided_by' => $this->userId, 'decided_at' => now()->subDays(21), 'converted_at' => now()->subDays(21),
        ]);
        foreach ([[$purchaseSuggestion, 'PURCHASE_CONVERTED'], [$productionSuggestion, 'PRODUCTION_CONVERTED']] as [$suggestionId, $event]) {
            $this->upsertId('mrp_suggestion_events', ['company_id' => self::COMPANY_ID, 'mrp_suggestion_id' => $suggestionId, 'event_type' => $event], [
                'from_status' => 'APPROVED', 'to_status' => 'CONVERTED', 'created_by' => $this->userId,
                'reason' => 'Conversão automática do cenário demonstrativo.', 'payload' => $this->json(['demo' => true]),
            ]);
        }

        $this->id['schedule'] = $this->upsertId('production_schedules', ['company_id' => self::COMPANY_ID, 'schedule_number' => 'PROG-BOAT-0001'], [
            'plant_id' => $this->id['plant'], 'version_number' => 1, 'status' => 'PUBLISHED',
            'reference_date' => now()->subDays(21)->toDateString(), 'mode' => 'finite', 'direction' => 'forward',
            'sequencing_rule' => 'priority_due_date', 'parameters' => $this->json(['include_setup' => true]),
            'source_run_key' => 'BOAT-DEMO-MRP-001', 'created_by' => $this->userId,
            'approved_by' => $this->userId, 'approved_at' => now()->subDays(21),
            'published_by' => $this->userId, 'published_at' => now()->subDays(21),
            'change_reason' => 'Programa demonstrativo do estaleiro.',
        ]);
        foreach (range(1, 5) as $sequence) {
            $operation = DB::table('production_order_operations')->where('id', $this->id['order_op_'.$sequence])->first();
            $this->upsertId('production_schedule_lines', ['production_schedule_id' => $this->id['schedule'], 'production_order_operation_id' => $operation->id], [
                'company_id' => self::COMPANY_ID, 'production_order_id' => $this->id['order'],
                'work_center_id' => $operation->work_center_id, 'production_resource_id' => $operation->production_resource_id,
                'planned_start_at' => $operation->planned_start_at, 'planned_end_at' => $operation->planned_end_at,
                'total_time_minutes' => $operation->total_time_minutes, 'capacity_time_minutes' => $operation->productive_time_minutes,
                'lead_time_minutes' => $operation->lead_time_minutes, 'segments' => $this->json([['type' => 'productive', 'minutes' => $operation->productive_time_minutes]]),
                'status' => 'COMPLETED', 'metadata' => $this->json(['demo' => true]),
            ]);
        }
        $this->upsertId('manufacturing_analytics_recommendations', ['company_id' => self::COMPANY_ID, 'production_order_operation_id' => $this->id['order_op_2']], [
            'routing_operation_id' => $this->id['routing_op_2'], 'standard_time_id' => $this->id['std_2'],
            'standard_time_version' => 1, 'status' => 'INVESTIGATE', 'current_time_minutes' => 1080,
            'suggested_time_minutes' => 1020, 'sample_size' => 8,
            'statistics' => $this->json(['mean' => 1045, 'median' => 1020, 'p90' => 1100, 'outliers' => 1]),
            'filters' => $this->json(['product_id' => $this->id['boat'], 'work_center_id' => $this->id['wc_lamination']]),
            'decision_reason' => 'Avaliar redução após estabilização do processo de laminação.',
            'decided_by' => $this->userId, 'decided_at' => now()->subDay(),
        ]);
    }

    /** @param array<string, mixed> $keys @param array<string, mixed> $values */
    private function upsertId(string $table, array $keys, array $values): int
    {
        $timestamp = now();
        $exists = DB::table($table)->where($keys)->exists();
        if (Schema::hasColumn($table, 'updated_at')) {
            $values['updated_at'] = $timestamp;
        }
        if (! $exists && Schema::hasColumn($table, 'created_at')) {
            $values['created_at'] = $timestamp;
        }

        DB::table($table)->updateOrInsert($keys, $values);

        return (int) DB::table($table)->where($keys)->value('id');
    }

    /** @param array<string, mixed> $keys */
    private function removeDuplicateDemoRows(string $table, array $keys): void
    {
        $ids = DB::table($table)->where($keys)->orderBy('id')->pluck('id');

        if ($ids->count() > 1) {
            DB::table($table)->whereIn('id', $ids->slice(1)->all())->delete();
        }
    }

    /** @param array<string, mixed> $value */
    private function json(array $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function assertEveryTenantTableHasData(): void
    {
        $database = DB::getDatabaseName();
        $tables = DB::select(
            'SELECT TABLE_NAME AS table_name FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND COLUMN_NAME = ? ORDER BY TABLE_NAME',
            [$database, 'company_id']
        );
        $empty = [];
        foreach ($tables as $row) {
            $data = array_change_key_case((array) $row, CASE_LOWER);
            $table = (string) $data['table_name'];
            if (! DB::table($table)->where('company_id', self::COMPANY_ID)->exists()) {
                $empty[] = $table;
            }
        }

        if ($empty !== []) {
            throw new RuntimeException('Boat demo did not populate tenant tables: '.implode(', ', $empty));
        }
    }
}
