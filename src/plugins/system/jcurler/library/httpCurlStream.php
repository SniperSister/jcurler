<?php

/**
 * HTTPCurlStream http/https stream wrapper for PHP core
 * Handle curl requests with standard calls (fileopen, file_get_contents, etc.)
 */

/**
 * Authors :		Christophe Dri
 * Inspired by:		Thomas Rabaix
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

class HTTPCurlStream implements IteratorAggregate, ArrayAccess
{
    private $_path;
    private $_mode;
    private $_options;
    private $_opened_path;
    private $_buffer;
    private $_pos;
    private $_ch;
    private $_metadata;
    public $context;

    /**
     * Open the stream
     *
     * @param string $path         path to open
     * @param string $mode         readonly or writeable
     * @param array  $options      array of options
     * @param string &$opened_path already openend path
     *
     * @return  boolean
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->_path = $path;
        $this->_mode = $mode;
        $this->_options = $options;
        $this->_opened_path = $opened_path;
        $this->_createBuffer($path);
        return true;
    }

    /**
     * Close the stream
     *
     * @return void
     */
    public function stream_close()
    {
        curl_close($this->_ch);
    }

    /**
     * Read the stream
     *
     * @param int $count number of bytes to read
     *
     * @return string content from pos to count
     */
    public function stream_read($count)
    {
        if (strlen($this->_buffer) == 0) {
            return false;
        }
        $read = substr($this->_buffer, $this->_pos, $count);
        $this->_pos += $count;
        return $read;
    }

    /**
     * write the stream, not implemented!
     *
     * @return boolean
     */
    public function stream_write()
    {
        if (strlen($this->_buffer) == 0) {
            return false;
        }
        return true;
    }

    /**
     * checks for end of file
     *
     * @return boolean true if eof else false
     */
    public function stream_eof()
    {
        if ($this->_pos >= strlen($this->_buffer)) {
            return true;
        }
        return false;
    }

    /**
     * returns current position of read pointer
     *
     * @return int the position of the current read pointer
     */
    public function stream_tell()
    {
        return $this->_pos;
    }

    /**
     * Flush stream data
     *
     * @return void
     */
    public function stream_flush()
    {
        $this->_buffer = null;
        $this->_pos = null;
    }

    /**
     * Stat the file, return only the size of the buffer
     *
     * @return array stat information
     */
    public function stream_stat()
    {
        $this->_createBuffer($this->_path);
        $stat = array(
            'size' => strlen($this->_buffer),
        );
        return $stat;
    }

    /**
     * Stat the url, return only the size of the buffer
     *
     * @param string $path path
     *
     * @return array stat information
     */
    public function url_stat($path)
    {
        $this->_createBuffer($path);
        $stat = array(
            'size' => strlen($this->_buffer),
        );
        return $stat;
    }

    /**
     * Create the buffer by requesting the url through cURL
     *
     * @param string $path path to read
     *
     * @return void
     */
    private function _createBuffer($path)
    {
        if ($this->_buffer) {
            return;
        }

        $options = stream_context_get_options($this->context);

        if (!empty($options['http']['curl_options'])
            && is_array($options['http']['curl_options'])
        ) {
            $curlOptions = $options['http']['curl_options'];
        } else {
            $curlOptions = array();

        }

        $curlOptions = array_replace(
            array(
                CURLOPT_FAILONERROR => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'PHP / HTTPCurlStream',
                CURLOPT_URL => $path,
                CURLOPT_HEADER => 1
            ),
            $curlOptions
        );

        if (defined('USE_PROXY') && USE_PROXY) {
            $curlOptions[CURLOPT_HTTPPROXYTUNNEL] = true;
            $curlOptions[CURLOPT_PROXY] = USE_PROXY;
        }

        $this->_ch = curl_init();
        curl_setopt_array($this->_ch, $curlOptions);

        $response = curl_exec($this->_ch);

        $headerSize = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);

        $this->_metadata = explode("\n", $header);
        $this->_buffer = substr($response, $headerSize);

        $this->_pos = 0;

    }

    /**
     * returns metadata array for stream_get_meta_data
     *
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_metadata);
    }

    /**
     * returns metadata array for stream_get_meta_data
     *
     * @param string $offset array key
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_metadata);
    }

    /**
     * gets metadata value
     *
     * @param string $offset array key
     *
     * @return boolean
     */
    public function offsetGet($offset )
    {
        return $this->_metadata[$offset];

    }

    /**
     * sets metadata value
     *
     * @param string $offset array key
     * @param string $value  new value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_metadata[$offset] = $value;
    }

    /**
     * unsets metadata key
     *
     * @param string $offset array key
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->_metadata[$offset]);
    }

}