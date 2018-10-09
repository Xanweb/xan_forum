<?php namespace XanForum\Service;

use XanForum\Service\Traits\ArrayTrait;

class BBCodeParser
{
    use ArrayTrait;

    public $parsers = [
        'bold' => [
            'pattern' => '/\[b\](.*?)\[\/b\]/s',
            'replace' => '<strong>$1</strong>',
            'content' => '$1',
        ],
        'italic' => [
            'pattern' => '/\[i\](.*?)\[\/i\]/s',
            'replace' => '<em>$1</em>',
            'content' => '$1',
        ],
        'underline' => [
            'pattern' => '/\[u\](.*?)\[\/u\]/s',
            'replace' => '<u>$1</u>',
            'content' => '$1',
        ],
        'linethrough' => [
            'pattern' => '/\[s\](.*?)\[\/s\]/s',
            'replace' => '<strike>$1</strike>',
            'content' => '$1',
        ],
        'font' => [
            'pattern' => '/\[font\=(.*?)\](.*?)\[\/font\]/s',
            'replace' => '<span style="font-family:$1">$2</span>',
            'content' => '$2',
        ],
        'size' => [
            'pattern' => '/\[size\=([1-7])\](.*?)\[\/size\]/s',
            'replace' => '<span style="font-size:$1">$2</span>',
            'content' => '$2',
        ],
        'color' => [
            'pattern' => '/\[color\=(#[A-f0-9]{6}|#[A-f0-9]{3})\](.*?)\[\/color\]/s',
            'replace' => '<span style="color:$1">$2</span>',
            'content' => '$2',
        ],
        'sup' => [
            'pattern' => '/\[sup\](.*?)\[\/sup\]/s',
            'replace' => '<sup>$1</sup>',
            'content' => '$1',
        ],
        'sub' => [
            'pattern' => '/\[sub\](.*?)\[\/sub\]/s',
            'replace' => '<sub>$1</sub>',
            'content' => '$1',
        ],
        'center' => [
            'pattern' => '/\[center\](.*?)\[\/center\]/s',
            'replace' => '<div style="text-align:center;">$1</div>',
            'content' => '$1',
        ],
        'left' => [
            'pattern' => '/\[left\](.*?)\[\/left\]/s',
            'replace' => '<div style="text-align:left;">$1</div>',
            'content' => '$1',
        ],
        'right' => [
            'pattern' => '/\[right\](.*?)\[\/right\]/s',
            'replace' => '<div style="text-align:right;">$1</div>',
            'content' => '$1',
        ],
        'justify' => [
            'pattern' => '/\[justify\](.*?)\[\/justify\]/s',
            'replace' => '<div style="text-align:justify;">$1</div>',
            'content' => '$1',
        ],
        'quote' => [
            'pattern' => '/\[quote\](.*?)\[\/quote\]/s',
            'replace' => '<blockquote>$1</blockquote>',
            'content' => '$1',
        ],
        'namedquote' => [
            'pattern' => '/\[quote\=(.*?)\](.*)\[\/quote\]/s',
            'replace' => '<blockquote><small>$1</small>$2</blockquote>',
            'content' => '$2',
        ],
        'link' => [
            'pattern' => '/\[url\](.*?)\[\/url\]/s',
            'replace' => '<a href="$1">$1</a>',
            'content' => '$1',
        ],
        'namedlink' => [
            'pattern' => '/\[url\=(.*?)\](.*?)\[\/url\]/s',
            'replace' => '<a href="$1">$2</a>',
            'content' => '$2',
        ],
        'image' => [
            'pattern' => '/\[img\](.*?)\[\/img\]/s',
            'replace' => '<img src="$1">',
            'content' => '$1',
        ],
        'orderedlistnumerical' => [
            'pattern' => '/\[list=1\](.*?)\[\/list\]/s',
            'replace' => '<ol>$1</ol>',
            'content' => '$1',
        ],
        'orderedlistalpha' => [
            'pattern' => '/\[list=a\](.*?)\[\/list\]/s',
            'replace' => '<ol type="a">$1</ol>',
            'content' => '$1',
        ],
        'unorderedlist' => [
            'pattern' => '/\[list\](.*?)\[\/list\]/s',
            'replace' => '<ul>$1</ul>',
            'content' => '$1',
        ],
        'listitem' => [
            'pattern' => '/\[\*\](.*)/',
            'replace' => '<li>$1</li>',
            'content' => '$1',
        ],
        'unorderedlist_v2' => [
            'pattern' => '/\[ul\](.*?)\[\/ul\]/s',
            'replace' => '<ul>$1</ul>',
            'content' => '$1',
        ],
        'listitem_v2' => [
            'pattern' => '/\[li\](.*?)\[\/li\]/s',
            'replace' => '<li>$1</li>',
            'content' => '$1',
        ],
        'code' => [
            'pattern' => '/\[code\](.*?)\[\/code\]/s',
            'replace' => '<code>$1</code>',
            'content' => '$1',
        ],
        'youtube' => [
            'pattern' => '/\[youtube\](.*?)\[\/youtube\]/s',
            'replace' => '<iframe width="560" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
            'content' => '$1',
        ],
        'email' => [
            'pattern' => '/\[email\=(.*)\](.*)\[\/email\]/',
            'replace' => '<a href="mailto: $1">$2</a>',
            'content' => '$2',
        ],
        'email_v2' => [
            'pattern' => '/\[email\](.*)\[\/email\]/',
            'replace' => '<a href="mailto: $1">$1</a>',
            'content' => '$1',
        ],
        'table' => [
            'pattern' => '/\[table class="table table-bordered"\](.*?)\[\/table\]/s',
            'replace' => '<table>$1</table>',
            'content' => '$1',
        ],
        'tabletr' => [
            'pattern' => '/\[tr\](.*?)\[\/tr\]/s',
            'replace' => '<tr>$1</tr>',
            'content' => '$1',
        ],
        'tableth' => [
            'pattern' => '/\[th\](.*?)\[\/th\]/s',
            'replace' => '<th>$1</th>',
            'content' => '$1',
        ],
        'tabletd' => [
            'pattern' => '/\[td\](.*?)\[\/td\]/s',
            'replace' => '<td>$1</td>',
            'content' => '$1',
        ],
        'linebreak' => [
            'pattern' => '/\r\n/',
            'replace' => '<br />',
            'content' => '',
        ],
        'horizontalline' => [
            'pattern' => '/\[hr\]/',
            'replace' => '<hr />',
            'content' => '',
        ],
    ];

    private $emoticons = ['teeth_smile', 'cry_smile', 'devil_smile', 'omg_smile', 'angel_smile', 'tongue_smile',  'angry_smile', 'cry_smile', 'angry_smile', 'regular_smile', 'whatchutalkingabout_smile'];
    private $altEmoticons = ['#\:D#', '#;\(#', '#&gt;\:\)#', '#\:O#', '#O\:#', '#\:P#', '#\:X#', '#\:\@#', '#\:\(\)#', '#\:\)#', '#\:\(#'];

    /**
     * Parses the BBCode string.
     *
     * @param  string $source String containing the BBCode
     *
     * @return string Parsed string
     */
    public function parse($source, $caseInsensitive = false)
    {
        //for the url have not tags [url]
        $source = $this->parserUrl($source);
        foreach ($this->parsers as $name => $parser) {
            $pattern = ($caseInsensitive) ? $parser['pattern'] . 'i' : $parser['pattern'];
            $source = $this->searchAndReplace($pattern, $parser['replace'], $source);
        }
        $source = $this->parseSmilies($source);
        $source = $this->parsePhpCodeForCkeditor($source);

        return $source;
    }

    /**
     * Remove all BBCode.
     *
     * @param  string $source
     *
     * @return string Parsed text
     */
    public function stripBBCodeTags($source)
    {
        foreach ($this->parsers as $name => $parser) {
            $source = $this->searchAndReplace($parser['pattern'] . 'i', $parser['content'], $source);
        }

        return $source;
    }

    /**
     * Searches after a specified pattern and replaces it with provided structure.
     *
     * @param  string $pattern Search pattern
     * @param  string $replace Replacement structure
     * @param  string $source Text to search in
     *
     * @return string Parsed text
     */
    protected function searchAndReplace($pattern, $replace, $source)
    {
        while (preg_match($pattern, $source)) {
            $source = preg_replace($pattern, $replace, $source);
        }

        return $source;
    }

    /**
     * Helper function to parse case sensitive.
     *
     * @param  string $source String containing the BBCode
     *
     * @return string Parsed text
     */
    public function parseCaseSensitive($source)
    {
        return $this->parse($source, false);
    }

    /**
     * Helper function to parse case insensitive.
     *
     * @param  string $source String containing the BBCode
     *
     * @return string Parsed text
     */
    public function parseCaseInsensitive($source)
    {
        return $this->parse($source, true);
    }

    /**
     * Limits the parsers to only those you specify.
     *
     * @param  mixed $only parsers
     *
     * @return BBCodeParser object
     */
    public function only($only = null)
    {
        $only = (is_array($only)) ? $only : func_get_args();
        $this->parsers = $this->arrayOnly($this->parsers, $only);

        return $this;
    }

    /**
     * Removes the parsers you want to exclude.
     *
     * @param  mixed $except parsers
     *
     * @return BBCodeParser object
     */
    public function except($except = null)
    {
        $except = (is_array($except)) ? $except : func_get_args();
        $this->parsers = $this->arrayExcept($this->parsers, $except);

        return $this;
    }

    /**
     * List of chosen parsers.
     *
     * @return array array of parsers
     */
    public function getParsers()
    {
        return $this->parsers;
    }

    /**
     * Sets the parser pattern and replace.
     * This can be used for new parsers or overwriting existing ones.
     *
     * @param string $name Parser name
     * @param string $pattern Pattern
     * @param string $replace Replace pattern
     */
    public function setParser($name, $pattern, $replace, $content = '$1')
    {
        $this->parsers[$name] = [
            'pattern' => $pattern,
            'replace' => $replace,
            'content' => $content,
        ];
    }

    public function smilies($whichOne)
    {
        // RP's Smilies (Emoticons) repository.
        // all in 8 bit format, otherwise it wouldn't survive
        $smiliesBundle = [
            $this->emoticons[0] => 'R0lGODlhEwATAPesAIJzGIt8GYx+J4t9MIp8MoV7SYV8T4h+T4h/VpWHP5aHPJ2NNZ+ONZyMPJ2OPaCOHaCOM6yaLqeWNKKROKiWMKmXNKuZM7WhKreiKrulIrumIr2nI7mkLb2oI76oIomCX4qCXZWIQ5SITJGIX4+HYI6HZ4uFaI6Ha4+IaZGJY5GKbpKLbpGLcZKNdZWPd5OOeJSOeJSQf8auJMevJMCqKcGrK8KrKcOtKcqyJMqzJcy0Jc+3JcixKsu0Ks21Ks+3KM+3KdS7Jte+JtK5Kdm/KN3DJ9rAKdzBKd7DKN3EK97EKd/EKODFKeDGKOHGKOHGKeLHKeTJKOTKKeXKKObKKObLKefMKejNKenOKOnOKevPKuvPK+vQK+zQKu3SK+7RKu7RK+7SK+/SKu/TKu/TK/LVKfLVKvLVK/PWKvPWLPTXK/XXKvTYK/bZK/faK/bZLPfZLPfaLPjaKvnbK/jaLPnbLPrbLPrcK/vdK/ncLPrcLPvcLPvdLPzdK/zeK/zdLPzeLJaRgJiTg5mViZmWiZyYip2aj56bj6Ofl6Ogl6Whmaeknammn6+sqK+tqbm4tb69vb++v8C/v8HAwMHAwcbFx8vKy8rJzM/P0dDP0dPT1d/f4+Pj5enp6+zs7u3t8O7u8e/v8e/v8vT09vX19/b2+Pj4+vr6+/v7/Pz8/f39/f7+/v///6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAK0ALAAAAAATABMAAAj+AFkJZJVK0yADBAQMKBDj0qmBEEMpCsFDC5w8br4AaVCoE0RWnmBcWPPHTp2Tdf7IsVGC08BSgWjgQUnzJKAfKkIJfEThDk09KPeY/IOBEatPI7AArUOHjJiTaZJsAVpGxCZKFfqghOMBhx49YG5wAQqIg6NDPf7QfBMHJZ+vdfgcacFCyZ6TS2ueiUJHTxYEKKAAjWOlTU09WgAs4dPlwAsjfPgICcBGjx07X/lceTBmTxUQi2oAqnNGQ44sYbxocbIjQxE9fIYIwuRgTh09aoLM2NBBho4mau76sQDJlAsifE6+pUMn7t3bVEiAYpUpgZm8NU+6YSBJ4CpHENA6JM++x02ERKgGnmqkAAkeQHu+/sEzZQEiUh9VWVoxgQcTKU/4IEEKkzz0kUCjVGLICR+YQEgkonwUEAA7',
            $this->emoticons[1] => 'R0lGODlhEwATAPfuAB8cIB8fICAcICAdICEdICEfICIeICIfICMfICYfIDccICUgBzItCSUhICgkIDEsITgxITs0IT02IUseIVoaIUc/InEdInYdIns3I0pBIk1EI05FI1FHI1JII1xRI1xSI2BOI2FVI2tfImpeJHxtFm9iJHNnJXRnJHZpLp0dIrQwI4R1GZB+GoFzJYV2JYZ3Jot7Jo5/JoV5PYZ6PIp7Nop9OrZKJYZ7R4V7ToR7Uod/V4R8WIR9Ws8fJMMhI8kmJOAcJOwZJPAcJJiGG5OCJpKEOZeIN52NNJ6POqGOHauXH6KPJ6OQJ6mWLamXLayYJ66aKKCQN6OSNa2aMLKdKLOfKLCcL7KeLLWfKLSgK7iiKLmkKrqlLL+pIr+pJ4eAXY+EVI2FXJOHQpKFR5CFSYqDZImDaIuEaIqFaoyGbY+IaZCJapKLcJCKdJCLd5GLdpGMd5OOfJOPfMSfKMenKcCqKcGqK8KsK8StKsSuK8unKcmqKcewJ8+3JcmxKc21KtGwKdG4Jte+J9K5KtO6KtW8Kte9Kde+Kdi/J9m/Kdi+KtzCJ9rAKd7EKN7EKd/FK+DFJ+DFKeLGK+PIKeTJKeXLKeXKK+bLKebMK+jMKOjNKenOKevPK+zPKuvQKevQK+zRK+3RK+7RKe7RK+7TKu/TK/LVKvPVK/PWK/PXKvPXK/PWLPTXLPXYKvXYK/bZKvfbK/fZLPfaLPjaK/nbLPrcK/vdK/ncLPrcLPvdLPveLPzeK/zeLP3eLP3fLP7fLP7fLf3gLP/iLZqWi5yZkKCclaGdlKGelaGelqKfl6ShmqajnKeknaikoKqooq2qpLCtqrSysba0tL28u8LBw8TDxMfHx8fGyMvKy8zLztPS1dnY3Nzc3+Li5OXl6Orq7Orq7ezs7u7u8PHx8/Pz9fT09fX19/b29/n5+vr6+/v7/Pz8/f39/v7+/////6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAO8ALAAAAAATABMAAAj+AN0JdNfOW7Q4X3SUGUZt3MCH7spBA2PFUCVNk/5IUWNtHcRvcpyIMjVkEbA+SWA1KuIM3cBxcOrU4oVogRJhLBhs+uUFxTN2BJldoTKIVycSgYB1WaHqFxQRNbC52zZGVAkmvHLJosVVFq9QJqoIYmMOmZ1chD4QsZTLVy9ftAqVgKBoF5JsPCjlAqWBQAYXT7AsOREBgIRHv+4ok3EKF6cNAwYIQNDAAAADBCpg4pXoDQ1XuEZ1mIygtGkBHELlinRmBiparEIAME1bgIdVuRi12XEJF64Ws2mXBjAiFi88xYjl4cVLi4MA0AuchpHLVpRr2sS0wvUJBIULFiZsJDhg4IEfX5DWkGNnbIsuXXtUBBECJIWCAy9kvTJSTWA4NHzwcoseNvzQgw8YxFDKLE0ko85A3aSRhSnBgALIHHRIwksmRxxzDkTiLEMGF46Qkoonh0wRxjTpQCQQO9w0YwYON+TghjTgQBQQADs=',
            $this->emoticons[2] => 'R0lGODlhEwATAPf4ADs0Cz01Cz84D0E5DEI6D0Q9DEU9DEg/DUQ9E0pBDUpCDUxDDk1FDk5FDk9GDlVLD1hOD1lPEFxSEV1TEVpWPmRYEmVZEmVaEmZbEmdbEmdcH2hcEmldEmleEWtfE2FXJW1gFHRmFXBmG3lrFXlrFnxuFnFpMHpvMHlvN3hvO3xwK3pwMnlwNVlXT21oRnpzS3p0Un95WH96XHZ0aX97YIFyF4FzF4R2F4d4GI5+GY5/GYd7NIZ7Nol9NYJ6SIF6UYF8XIR+WYR+W4F8YY+BGpSCGpWEGpiGG5uKGpqKHJyLHJ6NHY2AKZKEKZmJJZyLJ5mJLJ2NKZKEMKCPHaKRH6aTHqiVHqmWHqqXHqqYH62aH7CcH6aUIquZJrGeIbKeILWgILWhILeiILeiIbiiIbijIbmkIrqlJLumJL6oIb6oIr6oI4SAaIWAaIiEaYiEbo2KdpCNfsCqIsCrIsKrIsCqJMWvIcWuI8WvI8WuJMiwJMmyJMqxJMqzJMu0JM61JM+3JdC2JdC3JdC4JNC4JdG4JdG5JdG6JdK6JdS7Jde9Jde+JNa+Jti+Jdi/JtnAJtrAJdrCJt3DJt/FJt/FKODFJeDFJuLHKOPIJ+fNJuLIKOLJKOfMKenNJ+nNKerOKerPKOvPKuvQKuzQKOzQKu/SKe7SKu/TKvDTKvDTK/HUKvLVKvPXK/TXKfTXK/XXKvXYKvXYK/XZK/XYLPbZLPjaLPjbLPnbLPrbLPrdK/vdK/jcLPncLPrcLPvcLPvdLPveLfzdLPzeLPzeLf3eLP3fLP3fLf7fLf7gLf/hLf/iLYaFho+NhZGPgZKQgpGQhJaVh5eViJeVipeVi5iWi5uZkKemoqqpoaurpqyrpq6uq7Szsbq6ury8usHBwcrKzM3Ozs/Q0tDQz9LT1dXW197e4OTl5+np6+rq7Ozt7+7u8PHw8fDx8/Pz9PT09vb2+Pf4+fj4+fr6+/v7/Pz8/f39/v7+/v7+/////6usrQAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAPkALAAAAAATABMAAAj+APEJFPjuWzU2MYY045ZuoEN89sC5kYJGkSVIeaL82Bbv4TxsOxblomUqFClWvzI9iaNu4L1sTOwc8RDBAYMGDyrgSEMFjjuB4kx0KMDBSBY+gf6EUTIiAQQR1/Ctm0HggiNgdySIIibMRg5jnnQEoEBu2QcES5ApAwNgk69dGEIcS3ZIgIYWzMyIGUCCzqM9snrxYkSoUBEDNyK5eJNomKESCxREsABiw4QGBzKMiaWKhQxJvn79CiWIzBQiSb7o0VRrGK5XKto0+nXr1q9ZfrRgqaImVbDat1adoFZn2K1eqeQAMhXrVJ8rn3odx/TCGxRdvWrhuRRMOi5hlNRvuLo1bM0zdkEqndoCiDZw244Q9YLVIxy+bk1A1eAk/f0tW1bMUQY09eAjTzRcDIIKLv6RNwcSQpwzUDvOONFJMb8weIsvw7TiBRDlPASPNj500cgoq5QyyRk8SIPOQwOZYw0NKayAAgzTjEOPQwEBADs=',
            $this->emoticons[3] => 'R0lGODlhEwATAPfGAHFkFH5xF4Z4GIZ8N4V7OoZ9OYd+O4V8P4t+Mop/NYF7UYN+W5GBGpuJHJuKHIyAL46BMI6EOo+GPpKGLZSHKJSGLJuMLpyMLZGEM5GFNJGGN6WTHqqXHq+eJ6maK6ycKbOgILOhJ7aiIbqlIbqmIbmlJrqlJryoIrypIr6sJY2EQYWAXYiCXoiDYomFZYmGbImGbouIcI2JcZCMcZGNd5CNeJGPf5WSfMGsI8KtI8OvI8CuJcWuJcWwI8WyI8awJcazJcmyJMu3I8i0Jcq1Jcu0JMu1JM22I8y1Jcy3JM62Jc65Jc+4JdG5I9G6JdG7JtO6JdO7JdK6JtK8JNa9Jta+Jti/JNbAJ9nBJ9rCJtzDJd7FJ+DIKOHIKOLKKOfMKObPKunPJ+nPKerPKOzQKu3SKO7SKu/TK+/VKe7UKu/VK/DUKPDVKfHWKfHWK/TWK/PZKfTaKvXaKvbZK/fZK/faKvbZLPbaLPfbLPfcK/jaK/jaLPnbLPrbLPrcK/vdK/jcLPncLPrcLPrdLPvcLPvdLPvdLfveLfzdLPzdLfzeLPzeLfzfLPzfLf3eLP3eLf3fLP3fLf3gLJORgpWSgZeUgZaUhJqXh52bjZ2bj5yakp+elaSinKmooamooqyrpayrprCwq7Gwq7GxrbKxrLa1sre2s7y8u76+vsTDwsfHx8rKy83Nz87Ozs/Pz9HR09PT09PT1NXV1tXV2NnZ2tnZ293d3+/v8fHx8/X19/f3+Pj4+vn5+vr6+/v7+/v7/Pv8/Pz8/f39/f7+/v7+//7//////6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAMcALAAAAAATABMAAAj+AI0JFHjrlI0FClZMQoVroENjwVKxuPBjy5ctPCy0UCXsIS9OD7T8UURIECFFfqwg+ORrILFOFNoU4kOTjyCaiNZUEDWwVQI2N2sKMvOGpqAyBmQZ2zVjyiI+eG4WUiIgyB6ai4zc+PVKQx6oDYoUIgSlipurhQTJkVDLUwhIfA4FACC1UB8+dJykkfRhFCUiTwth2WCnJs1CDhws2oGpxhNDRgcZNnriSiMglywNWRR0sk0xPQBFSpEJVIdIZubcNSzoDg4vghh5KDUrQhwGKO4YGiSo0CI1JJJIhqPCVi8aQqggybGkCxguOjgwCcRnkY9KwYzBKoDm0RkpI0BNiAhCBnKhMAdoCSxGakIZR2ltJrqpaAwEUw6BhRrQRI+iQgAqUscRBJTSkUPDuPICBiZEkQUUJWQgQyzEPDRQLqtoAoMLMWzCii4PBQQAOw==',
            $this->emoticons[4] => 'R0lGODlhEwATAPfAAFNKIlRKI3xuJYJzJYV1Jop6Jot7Jo1/O4Z7R4Z8S4h8Q4p9QIh8RIZ+WZCAJpOCJpWEJpiHJ5uJJ56MJ5uMM52NNZ2NO5yNPaGOJ6KQJ6OQJ6WTJ6qYL62ZKK6bLKOSMaOSNqeWNLGdKLKdKLKfL7SfKLWgKLahKLejK7mkKbmmKbqlKrumKrqmLL+pK4+ETY2EVY2FXpGEQJKFQJOGRZSHQ4iBYYiBYouEZo+HZYyFaIyGao2GaoyGbY2HbY+Jc5GLc5SOdpOOeMGrKcGrLMOsKcOsK8StKcStK8WuKcevKcewKcixK8y0Ks21Ks61KNa9Kte+KNi/KNm/KdrBKdvBKdzCKd7EKd/FK9/GKuDGKeHHKeHHK+HIKeLIKOLIKePIK+bLKObLKebLK+fLK+jMK+rPKerPK+vPK+zQK+3RK+7RK+/TKe7SKu7SK+/TLPHVKfHUKvHVKvLVKvPWK/LVLPTXKvTXLPXYKvXYK/XYLPXZLPfaLPjaKvnbK/jaLPjbLPnbLPrbLPncK/rcK/rdK/vdK/rcLPrdLPvcLPvdLPzeK/zdLPzeLP3eLP3fLP7gLP/hLJeThJqViJuYjZyYi5yZj5+ckqCck6CdlKKfl6OflqKfmKWim6aknq6sqK+tqrCuq7a1s7i3tr69vsG/wMTDxMzLzc7N0M7O0NDP0dLS1dbW2d7e4eLi5OXl6Orq7evr7ezs7+/v8fDw8vPz9PT19vX19/X2+Pj4+fr6+/v7/P39/v3+//7+/v7+/////6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAMEALAAAAAATABMAAAj+AIEJBLZrlaUGCw4wuOGp1a+BEGFlqoFEDJ08cba0ePHJFkRgrniwwNPoUKCThxqx4SBp1sBYO540CvRH0clAhxQdKoRiUi5gvTatmBloyZE/OMss+SOI0IdRwFjRsIOySAARihydCJDm0KEwMWRxcuHokKA6EQSIOOTIBICugRaFIGXjSyA1gfScQWTyEJkjfE42alIJwZwSGPrexGnzZKIrPhTMGTBBkaDFmHF2wZGgDRQCEMY8MonZpCIqP3pUOQRGggona0jfdPOnkRFNoEg0cqTEwYMNiG4yytLhzyALp17BMHMI0J47DoY0ms7FgZNHUoDgAibqgx/TWApUZEihwUASRXJmpBKYi5KHPokCKXqjZEQRNJDgUADla2AtTBV4YUgjijwCSSSERCFDKLp8pEspOYDAhBVaTEHEBUGowstHA9FiyiVA6CBEJ6jc8lFAADs=',
            $this->emoticons[5] => 'R0lGODlhEwATAPfoABwYIBwZIB4bIB4cICAcICAdICEeICIeICIfICMeICMfICYfICkfIC0cIDEeICknISolIC0oIC0oITImITMtITQuITYwITcxITo1IUkdIVAaIVAeIXEbInceIk9FDk9GDlhOEE1HI1FIIlhOI1tRI1xSI2FWJGldJGpeJH1SI3hqFn1uFm1hJG9jJHJnJXdqJHdvOnZtPHlvMXluMnlvMn5yLXdwP3ZvRHdxS3lyR3lzTXlzTnp0Snt2Vn55XYIaIpQdIrEbI7YcI7wcI6czI4x8God5JYh5JYd9OYl+Noh9OoN6QoF4RIF5S4B5T4F6TYJ9XoJ9YIJ+ZcYZJModI98cJOcbJPQcJJOFJ5SFJ5eFJpeHJpqEJpqJJ5uKJZyNIp+PIZ6NJK6aH6CPIaKRJqSTJ6CQKKiVIqiWIaqXIqiYIK6bIa2aJa6cJbKdILCdJLWhJrikJLmlJLmkKbynKLyoI4eDaYaCboeEb4iEbIiFc4uJd42KecKtJsevI8KrKcOuKcavKcy0JMy2Js62JM+3Jcq0KtK6JtS7Jta+JdO7KtW8Kte+Kti/Jd7FJtnAKt7EKN/FKOLIJ+PJJuTKJuXLJ+bLJubLJ+jNJ+vPJ+jNKOjNK+rOK+vQKOvQKezQK+3RKu3RK+7RKu7SKe/TKvDUK/HUKvHWK/PVK/PWK/LWLPTXKvTXK/XYK/fZLPfaLPjaKvnbK/jaLPnbLPrbLPrcK/rdK/rcLPrdLPvcLPvdLPzdLPzeLPzeLf3eLP3fLP7gLZORhJKRhpSRhJaVjJiWipyaj5qZkZ6dk6SjnKSjnaSkn6moo66tq66uqq6uq7Oyr7KxsLSzsdHR09XV19bW2N7e4d/f4eDg4ubm6Ofo6ujo6enq7Orr7Ozs7u3t7+7v8fLy8/Ly9PX19vj4+fv7/P39/f7+/////6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAOkALAAAAAATABMAAAj+ANEJFMgtmp4dN3TgaZZtoEN05KQ5MSNI0iVJhco0cTbuobhhWCb9cgVK161dvjB54fNtILlgZ2Ll6uUHxCFds2bdqrVmT0d00LLAIiVLV6QPRXDmnGUrDDN02p5YYrVCzK1bkBDdWqozExNsz8jwkqXCg6hbupRy9cVG2R1Cu25FcpOKq11djXzwqHR1FrCtXNG+wnXLUwwbnXj9OdGFk69btG752qQFRSBdpmjk0FSqRAAFJOiEQvVpzogDAEyoGgVDSqJFFQ4oICBBBIkQDwgoMHDhkaMey9oAgoBAgYIEDTRw2LBAwYEIhuIcs7akDwXZChgAGSKkSofdFgZlIamGLtmXFgJkI8gwxcoVKg4GvEhj7By6cHnAsIAgoACGFEQE8cMELqgRhTcDbWPHGG8YsQUjpyjCxRFwoAHFNQ+Bg4wSckyySiurUFJHEsV085BA5lBDzA4z1CADDsJMU45DAQEAOw==',
            $this->emoticons[6] => 'R0lGODlhEwATAPerADw1C01FDmJWEXJkFHRnFXlrFn5wFnduOnduO3ZuPnluNHhvN3xxM3tyO3pyQ3tzRX94S3p0Un14WX15XIx8GYt/LoJ5PYZ8OoN6Q4F5R4R7QoB8Y4J+Y42ALZaHJpOFKJuMJJGDMJSGMKmWHqGQI6STJKeUI6eVI6aUJquYIquYJa2bJa6bJrKeJLWgILeiIbejJLmjIbqkIbulIbilJLylIbynIYaBYoWAZoaBZIeCZ4WBaIeDa4aCbIiFb4qGcoqHc8CpIsewI8y0JNG4JdK5Jde+I9S8JdS8JtW8Jte9Jta+Jde+Jdi/Jdm/JtnAJNnAJtvDJ9zCJt/FJ9/GJt/GKObKJuTKKObLKefLKejNJunOKO3RJ+3RKezQKu7RKu7SKO7SKe/TKO/TKe/TKvHVKfHUK/LVKfLVKvLVK/PXKfPWKvXZKvbYK/bZKvfaK/bZLPnbK/jaLPnbLPrbLPncK/rcLPvcLPvdLPveLPveLfzdLfzeLPzeLf3eLP3eLf3fLP3fLZOShZeWjJiWjZuZj56ck52clJ2clZ+elqKhmaSjm6SjnqWknqemoKemoamopLCwrLW1ssLCwsXFxcjJysrKy9bW2Nra3N3e4N/g4uPj5ebm6Ojp6u/v8PHx8/Pz9PP09fT09vX19vX19/f4+fn6+vz8/Pz8/fz9/f39/f///6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAKwALAAAAAATABMAAAj+AFcJFEjKUiIgOn4cqiRqoMNVqCjlCAGDiJMiNETcmHTq4ahDFYzE+YOn5J86TzoUCjXQ1CAQavDQwTOnpp2SbEgIKiVQ0gc3duaYUQJnjp0rU+zYeeMh0ipOEKzcmdMnCIAod/QYECDHqJYMmyCh4FOzj5AAVbJSGNB1DiAVjDYcoVmzDZaudsh4oVMTT5MJCbYorWmU8GCjdrocUIAmTZY+fCJLlhwIy5A1DBCEgUIghozPoEHXKDDjzAIJUr64QJKktWvXSEZkoRIBUYtAe0rq3l3STiAYhDBZEBOUsHHDZS5cUqXIRJ7j0PmkMJRq1aceL/IUP26Hj40dngYvdvJRgosfPEFvAgJzggenh6AcYWCxZMuYLUxWaFj06eFATY/g8EADDnDQSCYPBQQAOw==',
            $this->emoticons[7] => 'R0lGODlhEwAZAPf2AHtvKHluLXdtN3duO3dvP3tyOnx0P0pVW0tWXFBeZVFfZnZvQ3pzRnpzSn12TXx4W354WGpvcWtvcmdwdGpxdWpyd2tydnF5fWx4gW97gW97gneBh3eBiH+Eh3+KkH+LkX+MkX+MkoFyF4p6GY9/Got+LI1/K4d6MIh9NYh+P4p/PIB5SIF8W4N+XoR9WIN/Z4+CL5WFJ5GDLZSFLZiIJ5mJJp+PI5qLL52NKZqLMKaTHa+bH6GQJKORK6qYJbSfILWgILeiI7ijIbmkI7qlIbumIrymIb2nIbynIrynI4WBZ4eCZ4mEZoqFa4iEbomFcomFc4mFdYuHdcKrIsavI8myIsmyJMuzJMu0JMy0JM63JM+3JdG4I9K5I9O6I9O7I9G4JdO6JtO7JtS8Jta9Jte+Jti/JNi+Jti/Jtm/JtrAJdrBJNrAJ9vCJ9zDJN3DJN3DJd3DJuHHKOLHKOTKJ+fMJuPJKOXLKebLKefMKOvPKOvPKuvQKuvRKu3RKezQKu3RKu7RKe7SKvDUKvLVKfPXKfPWKvPWK/TWKfTXK/XXKvXYKfXYKvXZKvbZK/faKvbZLPfaLPnbK/jbLPnbLPrbLPncK/rdK/vdK/rcLPrdLPvcLPvdLPvdLfzeK/zdLPzdLfzeLPzeLf3eLP3eLf3fLP3fLYCFiIOLj4SMkIiOkYyPkZOQgZSShpiWjZiXjpeZnJabnpacnp6ck5ianJqeoZ2hoqCel6GgmKWkn6alnqGmqKWqrairrauusLGwrbOyr7a1tLm5t8C/v8DAwcHAwMDBwsHCw8PDwsbGx8fHyMnJycnJysvKzM7O0M/P0NDQ0tjY2dnZ2tzd4N/g4uLj5OPj5eTk5OXl5ebl5ebm5uvr6uvr6+zs7O3s7O7u8PDv7/Hx8fDw8vLx8fPy8vPz9fT09fb29fb29/b3+Pf3+fj4+vj5+vn5+vr6+/v6+vv7+/z8/f39/f7+/v7+///+/v///6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAPcALAAAAAATABkAAAj+AO0JFFjPWzhy4Made1dvoEN73YzB6nChwoQKF07ROubtYTYPCCSsstXLVy9bqyIc+KDN4TUQCiyokrWL165YqigkCIHt4TZjtVBt0IAhA4dUtYxxezgQXbNbUl48mZWsHFOB8ZYtgRGEixovSWa0GObuYTtdJ75YKvWJE6dRl9aYeGUVKy4ahDhR2suXEqdGNlqtEzisBKJMfRNnehTjl71vLuDoTay4zgprwXB8osw5lI9cULKE4ptJFF9OnU6jedCADmJKmbYAScPp0BQigBBXCiRggJ9KnTJlsjOFT6ZJYa44gk1JEQACeiiR+SOc1GtOoCg5EpTJUAAIcQakiQBjmnKmPiQA5VngaggnPELupE7MScuIOVaiQEPB6JMcHWdowolwnEzCBhWJSCIDMfGwUsQom+Cxww9j4GGHGFaUAYkpVTiRjj3VOPDFKJlE0sYRRiCBxR6lvWFANAM5w0AVbnEiSiacfPKJJ10UgMxD0ihRgxmFYDIKJou4wQMLz1xljjBNpJBDDzeowAQw4lxljzzwsEMNM8UoM4069MzjUEAAOw==',
            $this->emoticons[8] => 'R0lGODlhEwATAPfmAEtDI0xEImdcJHNGI25iJG9jJHFmJH9rJY83I4g6I68vI7InI7UpJL8hI4dOJIVaJZZDJI5+Jo9/Jo9/J5l9J6BdJapZJapnJqtvJod+UtAaI9wXJOIYJOQZJOgYJOsZJO4YJO0cJO8cJPIbJPEcJJKDJpqKJ5eJPZqLOJuMOKCNJ6SRJ6STLamYKKuYKK2aKKOTMaOTM6aVMaGRO6CSP6mXNa6cNLGeKLKeKLWhKLShLbGgM7GiMbKgMrekMYeAW4yCUI2EUYiAXo+GWZGFQZKGQZGGR5aLSZaKTZuPSJCIXouEYI+HaI+IbpCIZJKMcpOMcpGMepaVfsWnKcGrKcGsKcOtKcapKcexKcezK8myKcmzLMm1K8y3KM22Kc60Ks24Ks65Ks+4Ks+6KtC4KtG5KtK7K9O7KtK8KdO9KtW8KtW9KtW/K9a9Kti/Ktm/KtnAKNnAKtrAKtvCKNvEKtzCKt/FKuDHKODGK+HHK+HJK+HKK+PIKOLIKuPIKuXKKuXKK+bLKubMKOfNKOfMKunOKevPKujQK+/VKe7UKu/UK+/ULO/VLPDVKfLWK/HVLPLWLPPWLPTXKvTXLPPYKvHaLPXYKvfbKvTYLPXZLPbZLPfaLPXdLPfcLPnbK/nbLPrbLPvdK/jcLPncLPjfLPrcLPvdLPveLPzfK/zdLPzeLPzfLP3fLP7gLP7iLJSQgpqVg5iUhZqXiZqWipuXjJuZhJ2ZiZ6ajZ+cip+cj52akaGekqWjnaimoquoobGwrbKwr7Kxr7OysbWzsbW0s7e1tLi3t7m4t7u6vMC/wMHBwcLBwsfGyMrJy8zMzs3N0NbV2NjY29nZ3Nzc393d4d7e4Ofn6unp7O3t8PDw8vPz9fP09vb2+Pf3+Pf4+fn5+/r6+/v7/Pz8/f39/f7+/v7//////6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAOcALAAAAAATABMAAAj+AM0JFHjNWCwmS5roYqZtoENz3o4psWHGDyE7W2Y8cTbuYTZaMQr1iXOqlCYxjuAUARZuILdZOjyxikCgkyk5AMC0apRiWDmBxGCgMmVKQgFRpuIECKNqFSIj0MxZG3IHRx1VedyU+jTJC6RSavSggfVNWA8qAgCVKmXqk9tUn0ytMdAlSbQobRS1WATKrV+3pRiVoOMj2I9AqnKQgfvXraozOFxxyZXBkCk8Jh5t/VsKkwk2rcbYEvKHbQ4Vj1StLaUq0goXo1Rl2fWqTNtNNyZU0XNojxUJLzKVWrWjGLIaoT6xDTMAAYQEB9KM2koJCTVsTvi0NfVGAYgPGzBhkHKM5ZY4c8pQWGJ9pQGJEB4sVIorKEg1geF4yZDU6suCESJwcAEnqgxCxDIOddPLCXMkUoEGHTAwxSVaAJEMOQ+J0wwUNLDwgAMU8HCELNM85NA2z/hSixS4/CINOA8FBAA7',
            $this->emoticons[9] => 'R0lGODlhEwATAPe0AE5FDnJlFYN0F4V2GIV7TYZ8TIh8RYZ9UYd/WpuLN56ONpqLOaKPHamVH6qXH6CPNKeVLqyaLq+cLKCQNKSSMaaUMKeWNqKROaSTOKWUOKuZMqyaMbSfILOgLrSgK7ShLrulIb2nI4eAWo6EVY6FXY+GX5CGVI+HaYyHbo2HbpCIZ5KKZZGKaJKLbZOMa5GKcJOMcZONdJWPdpOOfZSPfsKsI8OsI8avI8SuJMWvK8awJMewK8y0JM62JcixKsuzKtC4JdO7JtO6KdO7Kdi/J9/EJ9vBKdzCKd3CKd3DKN3EKN/EKeHHJ+DGKOHHKOLIKOPIKOPIKeTJKeXJKeXKKOXKKebLKefLKOfLKebKKubLKuvPKOrOKurPKuvQKuzQKezQKu7SKu/TK/DTK/DUKvDUK/HVK/PWK/XYKvbZKvbZK/faKvfaK/XYLPXZLPbZLPfZLPjbK/jaLPjbLPnbLPrbLPncK/vdK/ncLPrcLPrdLPvcLPvdLPveLPzeK/3fK/zdLPzdLfzeLPzeLf3eLP3fLP7gLZuXiZyYjZ2ajp6ajp+ckZ+dkqGelKOgl6KgmKShmKWinK2qqK6tqrWzsLe1s7e2trm3t7y7ur+9vb++vsLBwcPCw8TDxMXExsfGx8fGyMnIy+Tk5+Xl6Orr7evs7u/w8vHx9PLy9PLy9fPz9fP09vX19/b29/f3+Pn5+vr6+/v7/Pz8/f7+/////6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAALUALAAAAAATABMAAAj+AGkJFHgKVKMYJ2Q4CrVqoENasDaxyLADSZQjOS60+CTroSpGCp74McQnD59Cf4osiNRqoCtEEtYIctIjD508UHjwQQPhESxasypRiJNnkA0AVvbMCSCgT541CjLRGjVii006YxhU2dOmQRI+N6mUKDWpgyA6aOsMysM20FU6gjRcQmEE7M08ZrBM6aLGLh0+QmgQAGOTTxggOHToCMEByBubebIgMECGzpkgNarA0cMHj5caNtzsycPlQAEwg24MKOP3ph4lDprw0SIixRFAYoi8RYtWDggphIbMkPTh7B7eyNGWFLTBkigTX3YnR5vnCglSsyhVsFNnOu88bB5AYBLY6pAHot75pInQ6NVAVIomULkjaDQfQXeYJIDE6uErTStY8MMSUizhAwYudBLLQwOZwkkiL6gAwyKepPJQQAA7',
            $this->emoticons[10] => 'R0lGODlhEwATAPfAAFpQEGRZEnFkFHZtNXdtNHZuOndvPXhuM3twLXhxPXlyRHlyRnp0SHt1Unt2VX54VH55VX14WH97YIN6PYZ7OoV7PYN7R4R7Q4F6ToJ7TYN8U4F9ZIR/ZJSDGpiGG5iHG5eHJpOEKJiIJpyMJZ2NKaaUHquYH62ZH6+bH6OSIqSTI6STJ6eVIqGQKKqXIqqYJayZIq2aJbOfILOfJLaiILSgJLeiJLijILijIbmkIb2nIb6oIr+pIoSAZYaCaoWCbIeDbIqHdo2Keo6Le5COf8GqIcCqIsCrIsSuI8ewI8WwJMawJMmzI8iwJMmzJMqzJMu0JNG5JdO6JtW8JdW9Jta9Jda8Jta9Jte/Jtm/J9nAJtrBJ9vCJtzCJ93DJt3EJ97FJuHHJuHHJ+DFKODHKOPJJeXKJubLJ+XMJ+bMJuXKKOjNJ+rPJ+vPKOvPKuvQKOzRKe7SKO7TKe/TKO/TKe7SKvDUKPDUKvLVKfLWK/TXKvTXK/XYK/bZK/faK/bZLPfaLPjbK/nbK/jaLPjbLPnbLPrbLPncK/ncLPrcLPrdLPvcLPvdLPvdLfveLPveLfzdLPzeLPzeLfzfLf3eLf3fLP3fLf/gLZKPgpSRhZiXjpyakZybk52blJ6clKGgl6SjnqWknaWkoK2tqq6tqLKxrrOzr7Ozsba2tbe2tNbW2NnZ29na3ODg4uLj5ePj5efn6efn6unp7Ozs7u7u8PDw8fHx8vLy8/X19vf3+Pf4+Pn5+v39/v7+/v7+//7//////6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAMEALAAAAAATABMAAAj+AIEJBPbLlSgJBg4UiNBpFa+BEHGRsvCiSps7cLjMqOCJFkRgtYaMYFOJUaJChhhRsuOix6uBuYjAOHSykM2bhiIV4eARWCkRggzdHHozEoxNvmRhMHNSUc1ENW0awkNB1akWkQologGFEaMsJwgNpWRDU5Ankgr1AVDCkiUPAYguAtPgQZiTfDpsYSRJBgpHROkQYIBG0kk/iGwOujlocSE9CCCIadKFEVGbidzgAGRIzgAhSj7gaHS5ECMyAsZI8uIAFYlATLBYHpqIzxEqeyzV4DRLQ5k+O5z8kcQI0qM6SLSYnDOBFbBUIQLt0WFiiRUpRnB8OemIxSeBuzJHpQjESE2SGzyu5FmUiFEOH7YG3sIEIk2lS5MkSYJUKY4KILB8pIspGawQxRlvrDFFDBeEEt9HAsUyyg8LJKDABqC00gtEAQEAOw==',
            $this->emoticons[11] => 'R0lGODlhEwAUAPfpAEA4DU5GD2VfPnFlFHJkFHZvPHhvMH90KXpxMH92NmdhRmhjSG5oS3BqRnFsVXRvVXVvVnRvXH52RHtzSn12THhzV3x2Wn54XXRwZXh0ZHp2Z3x3YHp2bXx4aoN1GIZ4IYN4K41/K4d9NoR6OYh8Noh/PoN7TJeGG4yAJY+EN46FOo2EP5KEJ5GGKZKEKZaJKpWILpSKLZmLJ5mLLJuNLJ+PK5CEN5mNMp2TM6aZH6CTJ6GSKqKTKaOTLKOXKaebLKibKquaKK+eKK2hIqygK7OgJrKiJ7mnJrqoJLqrJL2pIr6qIr6rJomAQ4mAR4uBRYqCT42FTYiDZoiEbI+KaoSCeYqGdYiFf4uIfo+Nf5WRe8CrJsGsJsKwJsSwJcaxJ8S1JMi2Jcq3Jcu6IdK7JtO9JdO8Jta/JtjBJtnDJdrCJNzGJtnEKN3FKN7FKODHJuPJJeLIKOPLKObNKenPKefQKevTJurQKezQKO/TKO7TKu3WKe7UKe/UKu7WKvDUKPDUK/LYKvPbKfPbK/HcKPXbKvbZK/baKffaLPXfKfbcKvjbKvnbLPrbLPnfK/rdK/reK/ndLfvdLPvfLPzeLfvgLPzhK/3hLP7hLf/jLYmHhY2Lio+OkZCOi5KQjpiWjpWTk5aVlJmYlJqYlpmYmpqYmZ+eoaCenaKhoaOioqmopKmoq6qoqaupq6uqqqqqra+urq6us7GwsrSztLW1trW1uba1ube2uLi3ubi3uri4ubm5u7y8wb+/w7+/xMC/wMC/w8HBw8PCw8LCxcLCxsXFycfGyMjHy8jIzMrKzczLzMzLz87OzszM0M/P0c7P09HR0dDQ09LS09PT19bW29jY29vb29rZ3Nvb3t/f4uLi4uHh5OTk5ufo6ujo6u7u7+7u8fDw8fHw8vLy8/X19vj4+fv7+/r7/P39/v///6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAOoALAAAAAATABQAAAj+ANMJFNjtFYcFAhRgMLUN3cCH5mw5CMHFjZw2XlgwWEXuYbpypRCUgYRojiRJlCSlOaBJ3EB0qUDsSXkmwBJJjBhRCoRiUzmBzgrUoXNHkp4BHiLlZCTJj4Fd6c5hQZLpBIE+kvrEWZqTUpgM4bBJ4ENJCQAyKXFyZTQoQbFbMCYxMmQGkFqmJxsxuqSj1KkgKR8dWpSW0h81avKgPHJllBBMeHo8sbEFzpsiTrRMifLlEpMqrHZYqhHqGbFPVKSoGjYuGrUKbIx0QkbCjolYv4TpSlZLGi1os5Rx+hFDFrgLY6DwYgZtWC5QwIJpE2aN1IsJ2dK1auHDUzNfWVRjAFlhxVivXhQ+iHIYrsMQIk1SdCl0SZCYGyVw5HjAbeA1CEkQ4ggleulUSSJgNHCMR9Vo4AIahUwioSJryBDBMh4J9I0rG4wwAw80iGABKt5k+JA40+wCCy7RgGMiRA69OFBAADs=',
            $this->emoticons[12] => 'R0lGODlhEwASAPfDAFFII1NKI1hOI2JWJHFlJX5xJn9yJYFzJYN0Jol5JpGCJ5KDJpODJpaFJ5qJJ5+NJ52QPZ+RPaCOJ6KQJ6SSJ6mWJ6eXNaKVOq6cM6maPrOeKLGfNbOiL7ejKLikKLmlKLqlKb6oKbGgMbSjNbelM72qMJWLS5SJTpWLTpOJU5+SQ5yQSpyRT5+STZ2RUJuRXZWNYpWNa5WPbpaOb5mQZZmSZ5aQcZmScpmTepmUeqOVQcGrKcGtKcKtKcCsMMSwKsawKcWwLcaxLcizLMm0Ksm0K8i0LM+3Ks64Kc64Ks+6KdG5KNC6KdG7KNO7K9a9KtjAKtnBKtrDKNrCKtzCKN/FKuHHK+HJKOTJKeTKK+TLK+bLKebMKefNK+jNK+nPKOnOK+vPK+3RKe7TKe/TK+3UKe/UK/DVKfHUKfDVK/PXK/PWLPbZKvfZKvbaKvTYLPXYLPXZLPbZLPfZLPbaLPfaLPfbLPjbKvjbK/nbLPrbLPjcK/ncK/rdKvveK/ncLPrcLPrdLPvcLPvdLPzeK/zfK/zdLPzeLPzfLP3eLP3fLP3gLP7gLP/hLJ+ag5+biKCcjKCcjaWhj6WhkqShlKekmammm6yon6ypn62qorCtpra0sLu5tb27ur69ur++vMC+vcHAwMLBwcTDwsjHx83MzdDQ0tPS1NPT1dbW2NfW2Nra3d7d4d7e4N/f4eLi5ebl6Obm6ujo7Orq7O7u8e/v8vHx9PLy9vTz9vT09vT09/n5+vr6+/r6/Pv7/Pv8/f39/v7+/v7+/////6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAMQALAAAAAATABIAAAj+AIcJFEgrFKQZMXBkSuVroMNhu0DB2OAEy5YqQVQ8cvVwWC5KFr4QMgQI0KBEd5ikKOWQVyUOfATBgTMoz5w1gw5xOaFqIKkLe/IA6kLByqA3FZIAOiTFRq5hum5cqZmHiAEBO6IsCPCgDiEMo4ah0tEnTx46Awh8gJIAgAcgcgZRcfRrUwlFZv80ANGIkQYHiw6ZRfOilqUigs3KqWN2jhyzZtu0iHVpSGLImDGzcSGrEwlEmE0CgqzHrBgat1itcANZUJgnZA4NOuIFkKElk4L1etRE8KAsBQ4gCKFFQY9DdyKcErjKRJlBaRZ0sDNlAgMeahD9iNRwmDBPEMY9mJEAxmSgOIMKIZExyyGwTyiUnMGT6FAiP19E5IDVcVgrSSyMIIQRPmRQAye49CcQMK+Iogkmm5hiS0cBAQA7',
            $this->emoticons[13] => 'R0lGODlhEwATAPfLAAAAAAIBAAUEAQYFAQcGAQ4MAxYDAx4FBRwYBR4bBSMeBywnCFIHDEQ8DV4tEElADV9OEWdbE3JlFHJmFocWFbETG7EcHLp/IZKCG5WEG5aJP5iLPKKPHa2ZIKOSMqCSNqGSOqOTOaiZOaqaOLmHIrCcIbOeIL2nI72qLr2qL76pLr6qLLKgNLajMbqnMY+ERo6EVYyDWY+GWpCFS5OIQ5WJQJWKS5uORJyOQJWKUZaNV5WMWo+HYI6HaJWOcJSOc5WPdZaQd5mTfpmTf8agJMajJMKsKsKtK8exJcOwKsewKcixK8iyK8m1K8y3Ks+3K9G4JdW8JtK6KtG8K9W+KNi/KtnAJ9zDKN3EKN3FKN7FKN/FKN7EKt/EKt/HKOHHKOLJKOPJKOPJKePKKenPKurPKuvQKuzRKu7SKu/UKfDVKfHVKfDUK/HUK/HWKvHWK/LWKvLWK/LXK/PXKvTXKfTXK/TYKvTYK/XYK/bZK/fbKvfaLPfbLPjbKvjaLPjbLPnbLPrbLPrdK/vdK/veK/rcLPrdLPvcLPvdLPvdLfvfLfzeK/zdLPzeLPzeLf3eLP3eLf3fLP3fLfrgLZyXh5+ajaGciqCckKKfkaKflaOglqOgl6Wilqekm6uona2qoqyqpKyqpa2rp6+tqLCuqrCuq7KxrrOwrrSysby6usbFxsfGyMfHyMfHycrJy8nJzMvLzM3Mzs7Oz8/O0M/P0tLS1NbV2Nzc3+Hh5OHh5ePj5ubm6Ofn6+jo6+np7PPz9fT09vb3+fj4+vn5+/v7+/z8/fz9/v39/v3+//7+/v7+/////6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAMwALAAAAAATABMAAAj+AJcJFPirVSYgPYBsegVsoMNlxWD5CLGkixguTED8mHXs4TBQG7YM+tMGEZ48gq7UKEVs4DFRHuwgKnQCiSQtJe4gcvPBVDKBsjTMKQQIkJk6jfaU8QOo0BoatZYFC2IFUVFAieRgiWO1aCMqQoTRwtHnaiEyERRMYEO0qJ4btkKpaHTVEQYBAAqYgHS1kYtTlKR0LYSmAYDDEgz1dYJpSJWujaAgOAzgQZu2iKZYqvSELiBJHQhQXiDmkNcmnFC18CwpQwDKCaJ0XcQi1S0bdIpK4jDAwAEGDryYDgQnRy5jl5TQLTQGAoUKFi7wKfrIiCZky3DBCGMV0RkiJIpJvCHK6EsMXQNZvQDTKFAhRZMUuW+UZUYsh8pcyTiihlCkRpEQksYKPMzykEC7eKLDCCgkkYIIO3zSy4Eu8aLKKJ2QsoovPzkUEAA7',
            $this->emoticons[14] => 'R0lGODlhEwATAPexAHttF3tuF5aHG5+QHpaJPZ+PNZuMOJ6QPqCQHaOTHqaTH6WUH6uZIKGRNaWUMqaWMqWWOKiXMKiYN6+eMbGeIbCeLLGfMbahIrekIrilIbmkIbikK7qmL7yoL7WiMY+ERIuBSomAUomAU4qDW5GGQ5KGQZmLQ5mMQpGHVZCHWJKJW4yFYoyGaY2HaJKKZZSNbZCKcpKMc5KMdpWPeJSOepaQe5eRfpmUf8CsI8OuJMOuLMmyJM+3Jcu1K8+5JtG5K9K5KNK6KtO8Kda9KNa/KNi/J9i/KtrBKdvBKt7FKd7GKODGKOHIKeLIKeLKKeLLKePKKeTJKeTKKOTLKefLKebNKevPKunQKurQKerRKuvQKu3RKezRK+3TKu7RKu7SKe/UKfHVKfDUKvHVK/LXK/PXKvTXK/XYK/faKvXYLPfcLPjbKvjaLPnbLPrbLPncKvncK/ndK/rdK/veK/jcLPncLPrcLPrdLPvcLPvdLPvdLfveLPveLfzdLPzeLPzeLfzfLPzfLf3fLJiTgpiUhZmUh5uWhpuXiJ2ZjZ2Zjp+bkJ+bkqajnKyqp62rp6+tqrCurLGvrbWzsbW0srW0s7i2tLi3tcC/v8PCxMTDxMvKys/P0tDQ0tHR1dPS1NTU1tTU2OLi5eXl6Ojo6+7u8O/v8vHx8/X19vb2+Pb3+Pf3+fn5+vn6+/z8/f39/v7+/v7//////6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAALIALAAAAAATABMAAAj+AGMJFJhqEyMaLGIowlRqoMNYrjzNOKADCZMkPSS4uLTqYStJJIi88ZPHDh4/c6QUWGRqIKxJBsDkaUOzZhs8ayogUiXw04cveGrWoWnHTRs3cBxQirXKBhA/RKMoSLMnA4+ZdrCkEAXKBJqaeYoEIBNIgIY/NAFZiPSIA9SaZ6i0saNFjB2aeYzIOPSjj825R0vWtHNFxA0jM2m6QctmDJc2eoK66QLCUBC/bdh42UEhQYIBDDD4uJM1RKMOUPMsAYAAxxMoWZzkWEAnzxEYnU58tWPlgho+eOwIz2OGjSAPj1TVGALVTeK/wregCBWLU4kwd//alBMBksBXjxo6lHlu006cDYRODWTliICSOX6E28kDCMuDQqQ8anoBoUeTKlMIMYEKlaDy0ECmZJJICyOsMIglozwUEAA7',
            $this->emoticons[15] => 'R0lGODlhFQATAPfJAIJ6OYd+OYh/PoN7QYJ6R4N9ToF6U4V/UoJ9Wo2BMIyDMJKHLZ2OJpmMKZOGM5WIMZ6SKKmaH62aH6STJ6WTJ6OTK6CTLaGVK6SWLaaaKa6fJKyaKLCdILGeILalJrekJ7emJLikJ7inJr2pI7+rI76qJr+uJIeBToqCRImCSYWAVY+KXoaAYoqGaYuHa4+KZo+NepCLaJGOdpKPfJSQfMOuI8CvJMOtKcSwJMeyJsmyJMm0Jcu2Jsy1Kcy6Jc+6Js+7KdC5JtG7JNC7JtC9JdO8J9W/J9a/JdG5KdC6KtK6KdS7KdW9KdbAJ9rCJ9zGJd/GJ9rBKdrDKN/IJt/LJuDKJuDMJ+TLJ+LJKeXKKOXLKeTMKubRJ+fQKOfRKOrQKurRKuvUKOzQK+3TK+7SKu7SK+3UK+7VKe/VK+/ULPLWKfPXK/HVLPPXLPHYKfPZKfTaKvTZLPXZLPXaLPbZLPbaLPfcLPjbLPnbLPrbLPjcK/ndK/jeK/nfK/ncLPrcLPrcLfrdLfvcLPvdLPvdLfveLPvfLPzdLPzdLfzeLPzeLfzfLPzfLf3eLP3fLfzgLPzgLZSSgZWSgpeVhZmWgpiVhpiWiZuZh52bjZ2bkZ+dkaCelaOimqSjmqSjm66tqLCvqrOyrrSzr7Ozsbi3tbu6try7uby7u76+vb+/vMTEw8XExcfGxsfHx8rKy8zLy8/Pz9ra3Nvb3N/f4eHh4+Tk5uXl5+jo6uzt7u7v8PP09vX19fT09vX29/b29/b39/f4+vn5+/r6+vr6+/v7/Pz8/f39/v7+/v7+/////6usrQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAUAAMoALAAAAAAVABMAAAj+AJMJFEgs1idJMmhwcqVroEOHsyYJ2JBjCI8PDlq0IvZwoCoCJNQsUoQIUSM9RxJs8tWRFYApjvIM+oMHz6BBid4wyDTMYa0CTxT9KXNDDM0lSPz8gbPAlENNIBzhydNmApOZSSjYsVlFBS6BtlKcyTM1TYg2NnsAKVTT0IVUAlFZMFQzDxsld/D8ibJFEKE/inxcQpbMkwepNfH4SezHT50aZAZRiRFMGCYTimomGiSI7J9BdKRE0HKIywperzqJkLpGhxMsaOSAMYKjiZ0/f6y8ALbrFIZHf75IKBJkBIcOP8YEqqmICCXCtFC4wXMHEKFCcczM+ZsYUoZSAo1cVbLBKHFNmuYFeTlxa6CsAV0GmZ+fpw8EUA9JKQiTeb5NPhrM0MtDxYgSgBB7ODLTIIosckUDkeTSUTLHwOLCAyU4kQUUO1RwQCi/TDgQL6tYwoIBCMAwSnsdBQQAOw==', ];

        if (true !== (array_key_exists($whichOne, $smiliesBundle))) {
            return; // hacking attempt
        } else {
            header("Content-type: image/gif");
            echo base64_decode($smiliesBundle[$whichOne]);
            flush();
            exit();
        }
    }

    /**
     * @param $source
     *
     * @return mixed
     */
    public function parseSmilies($source)
    {
        $smReplace = '<img title="smiley" alt="smiley" width="23" height="23"  src="/concrete/js/ckeditor4/vendor/plugins/smiley/images/';
        $smReplace2 = '.png" />';
        foreach ($this->altEmoticons as $index => $value) {
            $source = preg_replace($value, $smReplace . $this->emoticons[$index] . $smReplace2, $source);
        }
        //Particular case for smile sad
        $source = preg_replace('#([^cde])(\:/)(\[)#i', "$1" . $smReplace . "sad_smile" . $smReplace2, $source);

        return $source;
    }

    /**
     * for the url have not tags [url].
     *
     * @param $source
     *
     * @return mixed
     */
    private function parserUrl($source)
    {
        $pattern = '#[^=|\]](https?://)(.*)\s#';
        $source = preg_replace($pattern, "<a href='$1$2' >$1$2</a>", $source);

        return $source;
    }

    /**
     * parse php code to ckeditor.
     *
     * @param $source target source
     *
     * @return mixed
     */
    private function parsePhpCodeForCkeditor($source)
    {
        $source = preg_replace_callback("#\[php\](.+)\[/php\]#i", function ($matches) {
            return "<pre><code class=\"language-php\"> $matches[1] </code></pre>";
        }, $source);

        return $source;
    }
}
