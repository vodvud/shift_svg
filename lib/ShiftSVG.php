<?php

namespace SVGCreator\Lib;

include_once(SVG_UTIL_PATH . '/lib/inc.php');

/**
 * Class ShiftSVG
 * @package SVGCreator\Lib
 */
class ShiftSVG
{
    /**
     * Icons list
     * @var array
     */
    private $paths = [
        'empty' => ['fill' => '#e6e6e6', 'text' => ''],
        'am' => ['fill' => '#bd3e75', 'text' => 'M'],
        'pm' => ['fill' => '#ee6b2e', 'text' => 'A'],
        'night' => ['fill' => '#00c0ee', 'text' => 'N'],
        'en' => ['fill' => '#128b2a', 'text' => 'EN'],
        'day' => ['fill' => '#df9f04', 'text' => 'D'],
        'leave' => ['fill' => '#797979', 'text' => 'L'],
        'lt' => ['fill' => '#ff001b', 'text' => 'LT'],
        'er' => ['fill' => '#b70000', 'text' => 'ER'],
        'h' => ['fill' => '#8d8d00', 'text' => 'H'],
        'off' => ['fill' => '#37434d', 'text' => 'O'],
    ];

    /**
     * @param string $src
     * @return string
     */
    public function get($src = '')
    {
        $exp = explode('_', $src);
        $paths = [];
        foreach ($exp as $e) {
            if (isset($this->paths[$e])) {
                $paths[] = $this->paths[$e];
            }
        }

        return $this->render($paths);
    }

    /**
     * @param float $val
     * @return float
     */
    private function fix($val)
    {
        return ((abs($val) < 0.02) ? 0 : $val);
    }

    /**
     * @param float $val
     * @param int $multiple
     * @return float
     */
    private function multiply($val, $multiple = 100)
    {
        return ($val * $multiple);
    }

    /**
     * @param float $val
     * @return float
     */
    private function x($val)
    {
        $res = cos($val);
        $res = $this->multiply($res);
        $res = round($res, 2);
        return $this->fix($res);
    }

    /**
     * @param float $val
     * @return float
     */
    private function y($val)
    {
        $res = sin($val);
        $res = $this->multiply($res);
        $res = round($res, 2);
        return $this->fix($res);
    }

    /**
     * @param array $point [x, y]
     * @param int $radians
     * @return array [x, y]
     */
    private function rotate(array $point, $radians = 0)
    {
        list($x, $y) = $point;
        $cos = cos($radians);
        $sin = sin($radians);

        $px = round(($x * $cos - $y * $sin), 2);
        $py = round(($y * $cos + $x * $sin), 2);

        return [$this->fix($px), $this->fix($py)];
    }

    /**
     * @param string $str
     * @param float $size
     * @return float
     */
    private function fixSize($str, $size)
    {
        if (strlen($str) > 1) {
            $const = 0.67;
            $fontSize = round(($size / (strlen($str) * $const)), 3);
        } else {
            $fontSize = $size;
        }

        return $fontSize;
    }

    /**
     * @return \SVGCreator\Elements\Svg
     */
    private function makeSvg()
    {
        return new \SVGCreator\Elements\Svg([
            'xmlns' => 'http://www.w3.org/2000/svg',
            'width' => 24,
            'height' => 24,
            'viewBox' => '-100 -100 200 200'
        ]);
    }

    /**
     * @param array $params
     * @return \SVGCreator\Elements\Group
     */
    private function makeGroup(array $params = [])
    {
        return new \SVGCreator\Elements\Group($params);
    }

    /**
     * @return \SVGCreator\Elements\Group
     */
    private function makeGroupPath()
    {
        return $this->makeGroup([
            'stroke' => 'none'
        ]);
    }

    /**
     * @return \SVGCreator\Elements\Group
     */
    private function makeGroupText()
    {
        return $this->makeGroup([
            'text-anchor' => 'middle',
            'font-family' => 'Arial',
            'font-weight' => 'bold',
            'stroke' => 'none'
        ]);
    }

    /**
     * @param array $paths
     * @return string
     */
    private function render(array $paths)
    {
        $count = count($paths);
        if ($count === 0) {
            return '';
        }

        $const = (100 / $count);
        $size = round((100 / $count), 3) + 40;

        $groupPath = $this->makeGroupPath();
        $groupText = $this->makeGroupText();

        $stx = $this->x(0);
        $sty = $this->y(0);

        $r = (pi() / 2); //90 radians
        if ($count > 1) {
            //rotate start coordinates
            list($stx, $sty) = $this->rotate([$stx, $sty], $r);
        }

        $n = 0;
        foreach ($paths as $path) {
            $n = round(($n + $const), 3);

            $a = (pi() * 2) * ($n / 100);
            $x = $this->x($a);
            $y = $this->y($a);

            if ($count > 1) {
                $v = '0,1';
                //rotate coordinates
                list($x, $y) = $this->rotate([$x, $y], $r);

                $tx = ($x / 2);
                $ty = ($y / 2);

                //rotate text coordinates
                $tr = -((pi() * 2 / $count) / 2);
                list($tx, $ty) = $this->rotate([$tx, $ty], $tr);
            } else {
                $v = '1,0';
                $sty = -0.01;
                $tx = 0;
                $ty = 0;
            }

            //create path
            $groupPath->append(\SVGCreator\Element::PATH)
                ->attr('d', 'M0,0 L' . $stx . ',' . $sty . ' A100,100 0 ' . $v . ' ' . $x . ',' . $y . ' Z')
                ->attr('fill', $path['fill']);

            if (!empty($path['text'])) {
                $fontSize = $this->fixSize($path['text'], $size);
                $textFill = (isset($path['textFill']) ? $path['textFill'] : '#ffffff');

                //create text
                $groupText->append(\SVGCreator\Element::TEXT)
                    ->attr('x', $tx)
                    ->attr('y', $ty)
                    ->attr('dy', '0.333em')
                    ->attr('dx', 0)
                    ->attr('fill', $textFill)
                    ->attr('font-size', $fontSize . 'px')
                    ->text($path['text']);
            }

            //update start coordinates
            $stx = $x;
            $sty = $y;
        }

        $svg = $this->makeSvg();
        $svg->append($groupPath);

        if ($groupText->hasElements()) {
            $svg->append($groupText);
        }

        return $svg->getString();
    }
}
