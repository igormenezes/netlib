<?php

namespace Netlib\PrincipalBundle\Manager;

class BuscaManager {

    /**
     * Propriedades, que são utilizadas para realizar o filtro de interesses
     * @var array 
     */
    private $silabas = array('aa', 'ba', 'ca', 'da', 'ea', 'fa', 'ga', 'ha', 'ia', 'ja', 'ka', 'la', 'ma', 'na', 'oa', 'pa', 'qa', 'ra', 'sa', 'ta', 'ua', 'va', 'wa', 'xa', 'ya', 'za', 'ab', 'bb', 'cb', 'db', 'eb', 'fb', 'gb', 'hb', 'ib', 'jb', 'kb', 'lb', 'mb', 'nb', 'ob', 'pb', 'qb', 'rb', 'sb', 'tb', 'ub', 'vb', 'wb', 'xb', 'yb', 'zb', 'ac', 'bc', 'cc', 'dc', 'ec', 'fc', 'gc', 'hc', 'ic', 'jc', 'kc', 'lc', 'mc', 'nc', 'oc', 'pc', 'qc', 'rc', 'sc', 'tc', 'uc', 'vc', 'wc', 'xc', 'yc', 'zc', 'ad', 'bd', 'cd', 'dd', 'ed', 'fd', 'gd', 'hd', 'id', 'jd', 'kd', 'ld', 'md', 'nd', 'od', 'pd', 'qd', 'rd', 'sd', 'td', 'ud', 'vd', 'wd', 'xd', 'yd', 'zd', 'ae', 'be', 'ce', 'de', 'ee', 'fe', 'ge', 'he', 'ie', 'je', 'ke', 'le', 'me', 'ne', 'oe', 'pe', 'qe', 're', 'se', 'te', 'ue', 've', 'we', 'xe', 'ye', 'ze', 'af', 'bf', 'cf', 'df', 'ef', 'ff', 'gf', 'hf', 'if', 'jf', 'kf', 'lf', 'mf', 'nf', 'of', 'pf', 'qf', 'rf', 'sf', 'tf', 'uf', 'vf', 'wf', 'xf', 'yf', 'zf', 'ag', 'bg', 'cg', 'dg', 'eg', 'fg', 'gg', 'hg', 'ig', 'jg', 'kg', 'lg', 'mg', 'ng', 'og', 'pg', 'qg', 'rg', 'sg', 'tg', 'ug', 'vg', 'wg', 'xg', 'yg', 'zg', 'ah', 'bh', 'ch', 'dh', 'eh', 'fh', 'gh', 'hh', 'ih', 'jh', 'kh', 'lh', 'mh', 'nh', 'oh', 'ph', 'qh', 'rh', 'sh', 'th', 'uh', 'vh', 'wh', 'xh', 'yh', 'zh', 'ai', 'bi', 'ci', 'di', 'ei', 'fi', 'gi', 'hi', 'ii', 'ji', 'ki', 'li', 'mi', 'ni', 'oi', 'pi', 'qi', 'ri', 'si', 'ti', 'ui', 'vi', 'wi', 'xi', 'yi', 'zi', 'aj', 'bj', 'cj', 'dj', 'ej', 'fj', 'gj', 'hj', 'ij', 'jj', 'kj', 'lj', 'mj', 'nj', 'oj', 'pj', 'qj', 'rj', 'sj', 'tj', 'uj', 'vj', 'wj', 'xj', 'yj', 'zj', 'ak', 'bk', 'ck', 'dk', 'ek', 'fk', 'gk', 'hk', 'ik', 'jk', 'kk', 'lk', 'mk', 'nk', 'ok', 'pk', 'qk', 'rk', 'sk', 'tk', 'uk', 'vk', 'wk', 'xk', 'yk', 'zk', 'al', 'bl', 'cl', 'dl', 'el', 'fl', 'gl', 'hl', 'il', 'jl', 'kl', 'll', 'ml', 'nl', 'ol', 'pl', 'ql', 'rl', 'sl', 'tl', 'ul', 'vl', 'wl', 'xl', 'yl', 'zl', 'am', 'bm', 'cm', 'dm', 'em', 'fm', 'gm', 'hm', 'im', 'jm', 'km', 'lm', 'mm', 'nm', 'om', 'pm', 'qm', 'rm', 'sm', 'tm', 'um', 'vm', 'wm', 'xm', 'ym', 'zm', 'an', 'bn', 'cn', 'dn', 'en', 'fn', 'gn', 'hn', 'in', 'jn', 'kn', 'ln', 'mn', 'nn', 'on', 'pn', 'qn', 'rn', 'sn', 'tn', 'un', 'vn', 'wn', 'xn', 'yn', 'zn', 'ao', 'bo', 'co', 'do', 'eo', 'fo', 'go', 'ho', 'io', 'jo', 'ko', 'lo', 'mo', 'no', 'oo', 'po', 'qo', 'ro', 'so', 'to', 'uo', 'vo', 'wo', 'xo', 'yo', 'zo', 'ap', 'bp', 'cp', 'dp', 'ep', 'fp', 'gp', 'hp', 'ip', 'jp', 'kp', 'lp', 'mp', 'np', 'op', 'pp', 'qp', 'rp', 'sp', 'tp', 'up', 'vp', 'wp', 'xp', 'yp', 'zp', 'aq', 'bq', 'cq', 'dq', 'eq', 'fq', 'gq', 'hq', 'iq', 'jq', 'kq', 'lq', 'mq', 'nq', 'oq', 'pq', 'qq', 'rq', 'sq', 'tq', 'uq', 'vq', 'wq', 'xq', 'yq', 'zq', 'ar', 'br', 'cr', 'dr', 'er', 'fr', 'gr', 'hr', 'ir', 'jr', 'kr', 'lr', 'mr', 'nr', 'or', 'pr', 'qr', 'rr', 'sr', 'tr', 'ur', 'vr', 'wr', 'xr', 'yr', 'zr', 'as', 'bs', 'cs', 'ds', 'es', 'fs', 'gs', 'hs', 'is', 'js', 'ks', 'ls', 'ms', 'ns', 'os', 'ps', 'qs', 'rs', 'ss', 'ts', 'us', 'vs', 'ws', 'xs', 'ys', 'zs', 'at', 'bt', 'ct', 'dt', 'et', 'ft', 'gt', 'ht', 'it', 'jt', 'kt', 'lt', 'mt', 'nt', 'ot', 'pt', 'qt', 'rt', 'st', 'tt', 'ut', 'vt', 'wt', 'xt', 'yt', 'zt', 'au', 'bu', 'cu', 'du', 'eu', 'fu', 'gu', 'hu', 'iu', 'ju', 'ku', 'lu', 'mu', 'nu', 'ou', 'pu', 'qu', 'ru', 'su', 'tu', 'uu', 'vu', 'wu', 'xu', 'yu', 'zu', 'av', 'bv', 'cv', 'dv', 'ev', 'fv', 'gv', 'hv', 'iv', 'jv', 'kv', 'lv', 'mv', 'nv', 'ov', 'pv', 'qv', 'rv', 'sv', 'tv', 'uv', 'vv', 'wv', 'xv', 'yv', 'zv', 'aw', 'bw', 'cw', 'dw', 'ew', 'fw', 'gw', 'hw', 'iw', 'jw', 'kw', 'lw', 'mw', 'nw', 'ow', 'pw', 'qw', 'rw', 'sw', 'tw', 'uw', 'vw', 'ww', 'xw', 'yw', 'zw', 'ax', 'bx', 'cx', 'dx', 'ex', 'fx', 'gx', 'hx', 'ix', 'jx', 'kx', 'lx', 'mx', 'nx', 'ox', 'px', 'qx', 'rx', 'sx', 'tx', 'ux', 'vx', 'wx', 'xx', 'yx', 'zx', 'ay', 'by', 'cy', 'dy', 'ey', 'fy', 'gy', 'hy', 'iy', 'jy', 'ky', 'ly', 'my', 'ny', 'oy', 'py', 'qy', 'ry', 'sy', 'ty', 'uy', 'vy', 'wy', 'xy', 'yy', 'zy', 'az', 'bz', 'cz', 'dz', 'ez', 'fz', 'gz', 'hz', 'iz', 'jz', 'kz', 'lz', 'mz', 'nz', 'oz', 'pz', 'qz', 'rz', 'sz', 'tz', 'uz', 'vz', 'wz', 'xz', 'yz', 'zz');
    private $exclusao = array('', 'ela', 'elas', 'ele', 'eles', 'aquela', 'aquelas', 'aquele', 'aqueles', 'aquilo', 'isto', 'isso', 'aquilo', 'aqui', 'aí', 'alí', 'outro', 'outros', 'outra', 'estas', 'este', 'estes', 'esse', 'esses', 'a', 'as', 'à', 'às', 'àquelas', 'àquelas', 'àquilo', 'da', 'na', 'nas', 'naquele', 'naqueles', 'naquela', 'naquelas', 'naquilo', 'neste', 'nestes', 'nesse', 'nesses', 'pela', 'pelas', 'o', 'os', 'ao', 'aos', 'do', 'dos', 'no', 'nos', 'pelo', 'pelos', 'um', 'uns', 'dum', 'duns', 'num', 'nuns', 'uma', 'umas', 'duma', 'dumas', 'numa', 'numas', 'de', 'dela', 'delas', 'dele', 'deles', 'daquela', 'daquelas', 'daquele', 'daqueles', 'disto', 'disso', 'daquilo', 'daqui', 'daí', 'dali', 'doutro', 'doutros', 'doutras', 'destas', 'em', 'nela', 'nelas', 'nele', 'deles', 'por', 'per', 'com', 'conforme', 'contra', 'consoante', 'desde', 'durante', 'exceto', 'entre', 'mediante', 'perante', 'por', 'salvo', 'sem', 'sob', 'sobre', 'trás', 'após', 'até', 'que', 'é', 'e', 'nós', 'olá', 'oi', 'tudo', 'vai', 'ir', 'como', 'vou', 'bem', 'fazer', 'meu', 'meus', 'sim', 'não', 'eu', 'tenho', 'todos', 'minha', 'minhas', 'tô', 'quanto', 'quando', 'fico', 'sou', 'vez', 'dou', 'se', 'quero', 'muito', 'muitos', 'tomo', 'tiver', 'alguma', 'alguns', 'coisa');
    private $acentuacao = array('á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'Á', 'À', 'Ã', 'Â', 'É', 'Ê', 'Í', 'Ó', 'Ô', 'Õ', 'Ú', 'Ü');
    private $tirarAcentuacao = array('a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'A', 'A', 'A', 'A', 'E', 'E', 'I', 'O', 'O', 'O', 'U', 'U');

    /**
     * Função para filtrar os interesses, excluido caracteres desnecessarios, acentuação, separando por silabas, etc..
     * @param type $interesses
     * @return array = retorna um array com os valores e suas respectivas tabelas, separando por silabas ex ab => abobora, ar => arvore, etc..
     */
    public function filtrarInteresses($interesses) {
        try {
            $dados = array();
            $interesses = strtolower($interesses); //deixar todas as letras minusculas
            $interessesFiltrados = str_replace($this->acentuacao, $this->tirarAcentuacao, $interesses);

            $filtro1 = explode(' ', $interessesFiltrados); //filtra os espaçoes

            $filtro2 = implode('.', $filtro1); // filtra os pontos
            $filtro3 = explode('.', $filtro2); // ....

            $filtro4 = implode(',', $filtro3); //filtra as virgulas
            $filtro5 = explode(',', $filtro4); //....

            $filtro6 = implode('?', $filtro5); //filtra interrogação
            $filtro7 = explode('?', $filtro6); //....

            $filtro8 = implode('!', $filtro7); //filtra exclamação
            $filtro9 = explode('!', $filtro8); //.....

            $filtro10 = implode('/', $filtro9); //filtra barras
            $filtro11 = explode('/', $filtro10); //.....

            $filtro12 = implode(';', $filtro11); //filtra ;
            $filtro13 = explode(';', $filtro12); //.....

            $filtro14 = implode('|', $filtro13); //filtra |
            $filtro15 = explode('|', $filtro14); //.....

            $filtro16 = implode(':', $filtro15); //filtra :
            $valores = explode(':', $filtro16); //.....

            foreach ($valores as $chave => $val) {
                foreach ($this->exclusao as $valExclusao) {
                    if ($valExclusao == $val) {
                        unset($valores[$chave]);
                        break;
                    }
                }
            }

            return $valores;
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Não foi possivel filtrar os dados de interesses' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Não foi possivel filtrar os dados de interesses');
        }
    }

    /**
     * Função para filtrar resultado final da busca, para pegar apenas a foto de perfil de cada usuario.
     * Depois retornar na tela o apelido com a foto de cada usuario
     * @param array $dadosUsuarios = array dos dados do usuario, apelido e fotos
     * @return array = retorna um array contendo os dados filtrados da busca para exibição
     */
    public function filtrarFotosBusca($dadosUsuarios) {
        try {
            $i = 0;
            foreach ($dadosUsuarios as $arrayUsuarios => $array) {
                $arrayFotos = explode('|', $array['fotos']);
                $UsuariosFiltros[$i]['apelido'] = $array['apelido'];
                foreach ($arrayFotos as $val) {
                    if (substr($val, 0, 2) == 'PE') {
                        $UsuariosFiltros[$i]['foto'] = $val;
                    }
                }
                $i++;
            }

            return $UsuariosFiltros;
        } catch (\Exception $e) {
            file_put_contents('sistema/erro.log', date('d-m-Y') . ' ' . date('H:i') . ' ' . 'Erro ao filtrar resultados da busca' . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erro ao filtrar resultados da busca');
        }
    }

}
