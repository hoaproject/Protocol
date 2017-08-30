<?php

declare(strict_types=1);

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2017, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Hoa\Protocol\Test\Unit;

use Hoa\Protocol as LUT;
use Hoa\Protocol\Protocol as SUT;
use Hoa\Test;

/**
 * Test suite of the protocol class.
 */
class Protocol extends Test\Unit\Suite
{
    public function case_root_is_a_node(): void
    {
        $this
            ->when($result = SUT::getInstance())
            ->then
                ->object($result)
                    ->isInstanceOf(LUT\Node\Node::class);
    }

    public function case_default_tree(): void
    {
        $this
            ->when($result = SUT::getInstance())
            ->then
                ->object($result['Application'])->isInstanceOf(LUT\Node\Node::class)
                    ->object($result['Application']['Public'])->isInstanceOf(LUT\Node\Node::class)
                ->object($result['Data'])->isInstanceOf(LUT\Node\Node::class)
                    ->object($result['Data']['Etc'])->isInstanceOf(LUT\Node\Node::class)
                        ->object($result['Data']['Etc']['Configuration'])->isInstanceOf(LUT\Node\Node::class)
                        ->object($result['Data']['Etc']['Locale'])->isInstanceOf(LUT\Node\Node::class)
                    ->object($result['Data']['Lost+found'])->isInstanceOf(LUT\Node\Node::class)
                    ->object($result['Data']['Temporary'])->isInstanceOf(LUT\Node\Node::class)
                    ->object($result['Data']['Variable'])->isInstanceOf(LUT\Node\Node::class)
                        ->object($result['Data']['Variable']['Cache'])->isInstanceOf(LUT\Node\Node::class)
                        ->object($result['Data']['Variable']['Database'])->isInstanceOf(LUT\Node\Node::class)
                        ->object($result['Data']['Variable']['Log'])->isInstanceOf(LUT\Node\Node::class)
                        ->object($result['Data']['Variable']['Private'])->isInstanceOf(LUT\Node\Node::class)
                        ->object($result['Data']['Variable']['Run'])->isInstanceOf(LUT\Node\Node::class)
                        ->object($result['Data']['Variable']['Test'])->isInstanceOf(LUT\Node\Node::class)
                ->object($result['Library'])->isInstanceOf(LUT\Node\Library::class)
                ->string($result['Library']->reach())
                    ->isEqualTo(
                        dirname(__DIR__, 4) . DS . 'hoathis' . DS .
                        RS .
                        dirname(__DIR__, 4) . DS . 'hoa' . DS
                    );
    }

    public function case_resolve_not_a_hoa_path(): void
    {
        $this
            ->given($protocol = SUT::getInstance())
            ->when($result = $protocol->resolve('/foo/bar'))
            ->then
                ->string($result)
                    ->isEqualTo('/foo/bar');
    }

    public function case_resolve_to_non_existing_resource(): void
    {
        $this
            ->given($protocol = SUT::getInstance())
            ->when($result = $protocol->resolve('hoa://Application/Foo/Bar'))
            ->then
                ->string($result)
                    ->isEqualTo(SUT::NO_RESOLUTION);
    }

    public function case_resolve_does_not_test_if_exists(): void
    {
        $this
            ->given($protocol = SUT::getInstance())
            ->when($result = $protocol->resolve('hoa://Application/Foo/Bar', false))
            ->then
                ->string($result)
                    ->isEqualTo('/Foo/Bar');
    }

    public function case_resolve_unfold_to_existing_resources(): void
    {
        $this
            ->given($protocol = SUT::getInstance())
            ->when($result = $protocol->resolve('hoa://Library', true, true))
            ->then
                ->array($result)
                    ->contains(
                        dirname(__DIR__, 4) . DS . 'hoa'
                    );
    }

    public function case_resolve_unfold_to_non_existing_resources(): void
    {
        $this
            ->given(
                $parentHoaDirectory = dirname(__DIR__, 4),
                $protocol           = SUT::getInstance()
            )
            ->when($result = $protocol->resolve('hoa://Library', false, true))
            ->then
                ->array($result)
                    ->isEqualTo([
                        $parentHoaDirectory . DS . 'hoathis',
                        $parentHoaDirectory . DS . 'hoa'
                    ]);
    }
}
