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
use Hoa\Protocol\Wrapper as SUT;
use Hoa\Test;

/**
 * Test suite of the stream wrapper.
 */
class Wrapper extends Test\Unit\Suite
{
    public function case_stream_cast_for_select(): void
    {
        $this
            ->given($wrapper = new SUT())
            ->when($result = $wrapper->stream_cast(STREAM_CAST_FOR_SELECT))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    public function case_stream_cast_as_stream(): void
    {
        $this
            ->given($wrapper = new SUT())
            ->when($result = $wrapper->stream_cast(STREAM_CAST_AS_STREAM))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    public function case_stream_close(): void
    {
        $this
            ->given(
                $wrapper = new SUT(),
                $this->openFile($wrapper)
            )
            ->when($result = $wrapper->stream_close())
            ->then
                ->variable($result)
                    ->isNull()
                ->variable($wrapper->getStream())
                    ->isNull()
                ->variable($wrapper->getStreamName())
                    ->isNull();
    }

    public function case_stream_not_eof(): void
    {
        $this
            ->given(
                $wrapper = new SUT(),
                $this->openFile($wrapper, 'foo'),
                fseek($wrapper->getStream(), 0, SEEK_SET)
            )
            ->when($result = $wrapper->stream_eof())
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    public function case_stream_eof(): void
    {
        $this
            ->given(
                $this->function->feof = true,
                $wrapper = new SUT()
            )
            ->when($result = $wrapper->stream_eof())
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    public function case_stream_flush(): void
    {
        $this
            ->given(
                $wrapper = new SUT(),
                $this->openFile($wrapper)
            )
            ->when($result = $wrapper->stream_flush())
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    public function _case_stream_xxx_lock($operation): void
    {
        $this
            ->given(
                $this->function->flock = function ($resource, $operation) use (&$_resource, &$_operation) {
                    $_resource  = $resource;
                    $_operation = $operation;

                    if ($operation === LOCK_NB) {
                        return true;
                    }

                    return flock($resource, $operation);
                },
                $wrapper = new SUT(),
                $this->openFile($wrapper)
            )
            ->when($result = $wrapper->stream_lock($operation))
            ->then
                ->boolean($result)
                    ->isTrue()
                ->resource($_resource)
                    ->isStream()
                    ->isIdenticalTo($wrapper->getStream())
                ->integer($_operation)
                    ->isEqualTo($operation);
    }

    public function case_stream_shared_lock()
    {
        return $this->_case_stream_xxx_lock(LOCK_SH);
    }

    public function case_stream_exclusive_lock()
    {
        return $this->_case_stream_xxx_lock(LOCK_EX);
    }

    public function case_stream_release_lock()
    {
        return $this->_case_stream_xxx_lock(LOCK_UN);
    }

    public function case_stream_not_blocking_lock()
    {
        return $this->_case_stream_xxx_lock(LOCK_NB);
    }

    public function _case_metadata_touch_with_xxx_arguments($arguments, $path, $time, $atime): void
    {
        $this
            ->given(
                $this->function->touch = function ($path, $time, $atime) use (&$_path, &$_time, &$_atime) {
                    $_path  = $path;
                    $_time  = $time;
                    $_atime = $atime;

                    return true;
                },
                $wrapper = new SUT()
            )
            ->when($result = $wrapper->stream_metadata($path, STREAM_META_TOUCH, $arguments))
            ->then
                ->boolean($result)
                    ->isTrue()
                ->string($_path)
                    ->isEqualTo($path)
                ->variable($_time)
                    ->isEqualTo($time)
                ->variable($_atime)
                    ->isEqualTo($atime);
    }

    public function case_metadata_touch_with_no_argument()
    {
        return $this->_case_metadata_touch_with_xxx_arguments([], 'foo', null, null);
    }

    public function case_metadata_touch_with_time()
    {
        return $this->_case_metadata_touch_with_xxx_arguments([42], 'foo', 42, null);
    }

    public function case_metadata_touch_with_time_and_atime()
    {
        return $this->_case_metadata_touch_with_xxx_arguments([42, 777], 'foo', 42, 777);
    }

    public function _case_metadata_owner_xxx($owner): void
    {
        $this
            ->given(
                $this->function->chown = function ($path, $user) use (&$_path, &$_user) {
                    $_path = $path;
                    $_user = $user;

                    return true;
                },
                $path    = 'foo',
                $user    = 'gordon',
                $wrapper = new SUT()
            )
            ->when($result = $wrapper->stream_metadata('foo', $owner, $user))
            ->then
                ->boolean($result)
                    ->isTrue()
                ->string($path)
                    ->isEqualTo($_path)
                ->string($user)
                    ->isEqualTo($_user);
    }

    public function case_metadata_owner()
    {
        return $this->_case_metadata_owner_xxx(STREAM_META_OWNER);
    }

    public function case_metadata_owner_name()
    {
        return $this->_case_metadata_owner_xxx(STREAM_META_OWNER_NAME);
    }

    public function _case_metadata_group_xxx($grp): void
    {
        $this
            ->given(
                $this->function->chgrp = function ($path, $group) use (&$_path, &$_group) {
                    $_path  = $path;
                    $_group = $group;

                    return true;
                },
                $path    = 'foo',
                $group   = 'root',
                $wrapper = new SUT()
            )
            ->when($result = $wrapper->stream_metadata('foo', $grp, $group))
            ->then
                ->boolean($result)
                    ->isTrue()
                ->string($path)
                    ->isEqualTo($_path)
                ->string($group)
                    ->isEqualTo($_group);
    }

    public function case_metadata_group()
    {
        return $this->_case_metadata_group_xxx(STREAM_META_GROUP);
    }

    public function case_metadata_group_name()
    {
        return $this->_case_metadata_group_xxx(STREAM_META_GROUP_NAME);
    }

    public function case_metadata_access(): void
    {
        $this
            ->given(
                $this->function->chmod = function ($path, $mode) use (&$_path, &$_mode) {
                    $_path = $path;
                    $_mode = $mode;

                    return true;
                },
                $path    = 'foo',
                $mode    = 0755,
                $wrapper = new SUT()
            )
            ->when($result = $wrapper->stream_metadata('foo', STREAM_META_ACCESS, $mode))
            ->then
                ->boolean($result)
                    ->isTrue()
                ->string($path)
                    ->isEqualTo($_path)
                ->integer($mode)
                    ->isEqualTo($_mode);
    }

    public function case_metadata_default(): void
    {
        $this
            ->given(
                $option  = 0,
                $mode    = 0,
                $wrapper = new SUT()
            )
            ->when($result = $wrapper->stream_metadata('foo', $option, $mode))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    public function case_stream_open(): void
    {
        $this
            ->given(
                $this->function->fopen = function ($path, $mode, $options) use (&$_path, &$_mode, &$_options, &$_openedPath) {
                    $_path    = $path;
                    $_mode    = $mode;
                    $_options = $options;

                    return fopen($path, $mode, $options);
                },
                $wrapper = new SUT(),
                $path    = 'hoa://Test/Vfs/Foo?type=file',
                $mode    = 'r',
                $options = STREAM_USE_PATH
            )
            ->when($result = $wrapper->stream_open($path, $mode, $options, $openedPath))
            ->then
                ->boolean($result)
                    ->isTrue()
                ->string(SUT::realPath($path, true))
                    ->isEqualTo($_path)
                ->string($mode)
                    ->isEqualTo($_mode)
                ->integer($options)
                    ->isEqualTo($_options & STREAM_USE_PATH)
                ->resource($openedPath)
                    ->isStream()
                    ->isIdenticalTo($wrapper->getStream())
                ->string($wrapper->getStreamName())
                    ->isEqualTo('atoum://Foo');
    }

    public function case_stream_open_not_hoa_protocol(): void
    {
        $this
            ->given(
                $wrapper = new SUT(),
                $path    = LUT::NO_RESOLUTION,
                $mode    = 'r',
                $options = STREAM_USE_PATH
            )
            ->when($result = $wrapper->stream_open($path, $mode, $options, $openedPath))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    public function case_stream_open_not_a_resource(): void
    {
        $this
            ->given(
                $this->function->fopen = function ($path, $mode, $options) use (&$_path, &$_mode, &$_options, &$_openedPath) {
                    $_path    = $path;
                    $_mode    = $mode;
                    $_options = $options;

                    return fopen($path, $mode, $options);
                },
                $this->function->is_resource = false,

                $wrapper = new SUT(),
                $path    = 'hoa://Test/Vfs/Foo?type=file',
                $mode    = 'r',
                $options = STREAM_USE_PATH
            )
            ->when($result = $wrapper->stream_open($path, $mode, $options, $openedPath))
            ->then
                ->boolean($result)
                    ->isFalse()
                ->string(SUT::realPath($path, true))
                    ->isEqualTo($_path)
                ->string($mode)
                    ->isEqualTo($_mode)
                ->integer($options)
                    ->isEqualTo($_options & STREAM_USE_PATH)
                ->resource($openedPath)
                    ->isStream();
    }

    public function case_stream_read(): void
    {
        $this
            ->given(
                $this->function->fread = function ($resource, $count) use (&$_resource, &$_count) {
                    $_resource = $resource;
                    $_count    = $count;

                    return fread($resource, $count);
                },
                $wrapper = new SUT(),
                $count   = 42,
                $this->openFile($wrapper, str_repeat('@', $count))
            )
            ->when($result = $wrapper->stream_read($count))
            ->then
                ->string($result)
                    ->hasLength($count)
                ->resource($_resource)
                    ->isStream()
                    ->isIdenticalTo($wrapper->getStream())
                ->integer($_count)
                    ->isEqualTo($count);
    }

    public function _case_stream_seek_xxx($offset, $whence)
    {
        return
            $this
                ->given(
                    $this->function->fseek = function ($resource, $offset, $whence) use (&$_resource, &$_offset, &$_whence) {
                        $_resource = $resource;
                        $_offset   = $offset;
                        $_whence   = $whence;

                        return fseek($resource, $offset, $whence);
                    },
                    $wrapper = new SUT(),
                    $this->openFile($wrapper, 'foobar')
                )
                ->when($result = $wrapper->stream_seek($offset, $whence))
                ->then
                    ->boolean($result)
                        ->isTrue()
                    ->resource($_resource)
                        ->isStream()
                        ->isIdenticalTo($wrapper->getStream())
                    ->integer($offset)
                        ->isEqualTo($_offset)
                    ->integer($whence)
                        ->isEqualTo($_whence)
                    ->integer(ftell($wrapper->getStream()));
    }

    public function case_stream_seek_set()
    {
        return
            $this
                ->_case_stream_seek_xxx(3, SEEK_SET)
                ->isEqualTo(3);
    }

    public function case_stream_seek_current()
    {
        return
            $this
                ->_case_stream_seek_xxx(4, SEEK_CUR)
                ->isEqualTo(4);
    }

    public function case_stream_seek_end()
    {
        return
            $this
                ->_case_stream_seek_xxx(-4, SEEK_END)
                ->isEqualTo(2);
    }

    public function case_stream_stat(): void
    {
        $this
            ->given(
                $this->function->fstat = function ($resource) use (&$_resource) {
                    $_resource = $resource;

                    return fstat($resource);
                },
                $wrapper = new SUT(),
                $this->openFile($wrapper)
            )
            ->when($result = $wrapper->stream_stat())
            ->then
                ->array($result)
                ->resource($_resource)
                    ->isStream()
                    ->isIdenticalTo($wrapper->getStream());
    }

    public function case_stream_tell(): void
    {
        $this
            ->given(
                $this->function->ftell = function ($resource) use (&$_resource) {
                    $_resource = $resource;

                    return ftell($resource);
                },
                $wrapper = new SUT(),
                $this->openFile($wrapper, 'foo'),
                $wrapper->stream_seek(2)
            )
            ->when($result = $wrapper->stream_tell())
            ->then
                ->integer($result)
                    ->isEqualTo(2)
                ->resource($_resource)
                    ->isStream()
                    ->isIdenticalTo($wrapper->getStream());
    }

    public function case_stream_truncate(): void
    {
        $this
            ->given(
                $this->function->ftruncate = function ($resource, $size) use (&$_resource, &$_size) {
                    $_resource = $resource;
                    $_size     = $size;

                    return ftruncate($resource, $size);
                },
                $wrapper = new SUT(),
                $this->openFile($wrapper, 'foobar'),
                $size = 3
            )
            ->when($result = $wrapper->stream_truncate($size))
            ->then
                ->boolean($result)
                    ->isTrue()
                ->resource($_resource)
                    ->isStream()
                    ->isIdenticalTo($wrapper->getStream())
                ->integer($size)
                    ->isEqualTo($_size)
                ->integer($wrapper->stream_tell())
                    ->isEqualTo(0)
                ->let($wrapper->stream_seek(0, SEEK_END))
                ->integer($wrapper->stream_tell())
                    ->isEqualTo(3);
    }

    public function case_stream_write(): void
    {
        $this
            ->given(
                $this->function->fwrite = function ($resource, $data) use (&$_resource, &$_data) {
                    $_resource = $resource;
                    $_data     = $data;

                    return fwrite($resource, $data);
                },
                $wrapper = new SUT(),
                $wrapper->stream_open('hoa://Test/Vfs/Foo?type=file', 'wb+', STREAM_USE_PATH, $openedPath),
                $data = 'foo'
            )
            ->when($result = $wrapper->stream_write($data))
            ->then
                ->integer($result)
                    ->isEqualTo(strlen($data))
                ->resource($_resource)
                    ->isStream()
                    ->isIdenticalTo($wrapper->getStream())
                ->string($_data)
                    ->isEqualTo($data)
                ->let($wrapper->stream_seek(0))
                ->string($wrapper->stream_read(3))
                    ->isEqualTo($data);
    }

    public function case_dir_closedir(): void
    {
        $this
            ->given(
                $wrapper = new SUT(),
                $this->openDirectory($wrapper)
            )
            ->when($result = $wrapper->dir_closedir())
            ->then
                ->variable($result)
                    ->isNull()
                ->variable($wrapper->getStream())
                    ->isNull()
                ->variable($wrapper->getStreamName())
                    ->isNull();
    }

    public function case_dir_opendir(): void
    {
        $this
            ->given(
                $this->function->opendir = function ($path) use (&$_path) {
                    $_path = $path;

                    return opendir($path);
                },
                $wrapper = new SUT(),
                $path    = 'hoa://Test/Vfs/Bar?type=directory',
                $options = 0
            )
            ->when($result = $wrapper->dir_opendir($path, $options))
            ->then
                ->boolean($result)
                    ->isTrue()
                ->string(SUT::realPath($path, true))
                    ->isEqualTo($_path)
                ->resource($wrapper->getStream())
                    ->isStream()
                ->string($wrapper->getStreamName())
                    ->isEqualTo('atoum://Bar');
    }

    public function case_dir_opendir_not_a_resource(): void
    {
        $this
            ->given(
                $this->function->opendir = function ($path) use (&$_path) {
                    $_path = $path;

                    return false;
                },
                $wrapper = new SUT(),
                $path    = 'hoa://Test/Vfs/Bar?type=directory',
                $options = 0
            )
            ->when($result = $wrapper->dir_opendir($path, $options))
            ->then
                ->boolean($result)
                    ->isFalse()
                ->string(SUT::realPath($path, true))
                    ->isEqualTo($_path)
                ->variable($wrapper->getStream())
                    ->isNull()
                ->variable($wrapper->getStreamName())
                    ->isNull();
    }

    public function case_dir_readdir(): void
    {
        $this
            ->given(
                $this->function->readdir = function ($resource) use (&$_resource) {
                    $_resource = $resource;

                    return readdir($resource);
                },
                $wrapper = new SUT(),
                $this->openDirectory($wrapper, ['Baz', 'Qux'])
            )
            ->when($result = $wrapper->dir_readdir())
            ->then
                ->string($result)
                    ->isEqualTo('Baz')
                ->resource($_resource)
                    ->isIdenticalTo($wrapper->getStream());
    }

    public function case_dir_readdir_until_eod(): void
    {
        $this
            ->given(
                $this->function->readdir = function ($resource) use (&$_resource) {
                    $_resource = $resource;

                    return readdir($resource);
                },
                $wrapper = new SUT(),
                $this->openDirectory($wrapper, ['Baz', 'Qux'])
            )
            ->when($result = $wrapper->dir_readdir())
            ->then
                ->string($result)
                    ->isEqualTo('Baz')
                ->resource($_resource)
                    ->isIdenticalTo($wrapper->getStream())

            ->when($result = $wrapper->dir_readdir())
            ->then
                ->string($result)
                    ->isEqualTo('Qux')

            ->when($result = $wrapper->dir_readdir())
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    public function case_dir_rewinddir(): void
    {
        $this
            ->given(
                $this->function->rewinddir = function ($resource) use (&$_resource) {
                    $_resource = $resource;

                    return rewinddir($resource);
                },
                $wrapper = new SUT(),
                $this->openDirectory($wrapper, ['Baz']),
                $wrapper->dir_readdir()
            )
            ->when($result = $wrapper->dir_rewinddir())
            ->then
                ->variable($result)
                    ->isNull()

            ->when($result = $wrapper->dir_readdir())
            ->then
                ->string($result)
                    ->isEqualTo('Baz')
                ->resource($_resource)
                    ->isIdenticalTo($wrapper->getStream());
    }

    public function case_dir_mkdir(): void
    {
        $this
            ->given(
                $this->function->mkdir = function ($path, $mode, $options) use (&$_path, &$_mode, &$_options) {
                    $_path    = $path;
                    $_mode    = $mode;
                    $_options = $options;

                    return true;
                },
                $wrapper = new SUT(),
                $this->openDirectory($wrapper),
                $path    = 'Baz',
                $mode    = 0755,
                $options = STREAM_MKDIR_RECURSIVE
            )
            ->when($result = $wrapper->mkdir($path, $mode, $options))
            ->then
                ->boolean($result)
                    ->isTrue()
                ->string($_path)
                    ->isEqualTo($path)
                ->integer($_mode)
                    ->isEqualTo($_mode)
                ->integer($_options)
                    ->isEqualTo($options | STREAM_MKDIR_RECURSIVE);
    }

    public function case_rename(): void
    {
        $this
            ->given(
                $this->function->rename = function ($from, $to) use (&$_from, &$_to) {
                    $_to   = $to;
                    $_from = $from;

                    return rename($from, $to);
                },
                $wrapper = new SUT(),
                $this->openFile($wrapper),
                $from    = 'hoa://Test/Vfs/Foo?type=file',
                $to      = 'hoa://Test/Vfs/Oof?type=file'
            )
            ->when($result = $wrapper->rename($from, $to))
            ->then
                ->boolean($result)
                    ->isTrue()
                ->string($_from)
                    ->isEqualTo(SUT::realPath($from))
                ->string($_to)
                    ->isEqualTo(SUT::realPath($_to, false));
    }

    public function case_rmdir(): void
    {
        $this
            ->given(
                $this->function->rmdir = function ($path) use (&$_path) {
                    $_path = $path;

                    return rmdir($path);
                },
                $wrapper = new SUT(),
                $this->openDirectory($wrapper)
            )
            ->when($result = $wrapper->rmdir('hoa://Test/Vfs/Bar?type=directory', 0))
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    public function case_rmdir_a_file(): void
    {
        $this
            ->given(
                $wrapper = new SUT(),
                $this->openFile($wrapper)
            )
            ->when($result = $wrapper->rmdir('hoa://Test/Vfs/Foo?type=file', 0))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    public function case_unlink(): void
    {
        $this
            ->given(
                $wrapper = new SUT(),
                $this->openFile($wrapper)
            )
            ->when($result = $wrapper->unlink('hoa://Test/Vfs/Foo?type=file'))
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    public function case_rmdir_a_directory(): void
    {
        $this
            ->given(
                $wrapper = new SUT(),
                $this->openDirectory($wrapper)
            )
            ->when($result = $wrapper->unlink('hoa://Test/Vfs/Bar?type=directory'))
            ->then
                ->boolean($result)
                    ->isFalse();
    }

    public function case_url_stat(): void
    {
        $this
            ->given(
                $this->function->stat = function ($path) use (&$_path) {
                    $_path = $path;

                    return stat($path);
                },
                $wrapper = new SUT(),
                $this->openFile($wrapper),
                $path = 'hoa://Test/Vfs/Foo?type=file'
            )
            ->when($result = $wrapper->url_stat($path, 0))
            ->then
                ->let(
                    $keys = [
                        'dev',
                        'ino',
                        'mode',
                        'nlink',
                        'uid',
                        'gid',
                        'rdev',
                        'size',
                        'atime',
                        'mtime',
                        'ctime',
                        'blksize',
                        'blocks'
                    ]
                )
                ->array($result)
                    ->hasSize(26)
                    ->hasKeys($keys)
                    ->hasKeys(array_keys($keys))
                ->string($_path)
                    ->isEqualTo(SUT::realPath($path));
    }

    public function case_url_stat_not_hoa_protocol(): void
    {
        $this
            ->given(
                $wrapper = new SUT(),
                $path    = LUT::NO_RESOLUTION
            )
            ->when(function () use ($wrapper, $path): void {
                $wrapper->url_stat($path, 0);
            })
            ->then
                ->error()
                    ->exists();
    }

    protected function openFile(SUT $wrapper, $content = '')
    {
        $wrapper->stream_open('hoa://Test/Vfs/Foo?type=file', 'wb+', STREAM_USE_PATH, $openedPath);
        fwrite($openedPath, $content, strlen($content));
        fseek($openedPath, 0, SEEK_SET);

        return $wrapper;
    }

    protected function openDirectory(SUT $wrapper, array $children = [])
    {
        $wrapper->dir_opendir('hoa://Test/Vfs/Bar?type=directory', 0);

        foreach ($children as $child) {
            LUT::getInstance()->resolve('hoa://Test/Vfs/Bar/' . $child . '?type=file');
        }

        return $wrapper;
    }
}
