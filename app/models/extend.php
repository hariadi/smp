<?php

class Extend extends Base {

	public static $table = 'extend';

	public static function field($type, $key, $id = -1) {
		$field = Query::table(static::table())
			->where('type', '=', $type)
			->where('key', '=', $key)
			->fetch();

		if($field) {
			$meta = Query::table(static::table($type . '_meta'))
				->where($type, '=', $id)
				->where('extend', '=', $field->id)
				->fetch();

			$field->value = Json::decode($meta ? $meta->data : '{}');
		}

		return $field;
	}

	public static function value($extend, $value = null) {
		switch($extend->field) {
			case 'text':
				if( ! empty($extend->value->text)) {
					$value = $extend->value->text;
				}
				break;

			case 'html':
				if( ! empty($extend->value->html)) {
					$md = new Markdown;

					$value = $md->transform($extend->value->html);
				}
				break;

			case 'image':
			case 'file':
				if( ! empty($extend->value->filename)) {

					$file = asset('content/' . $extend->value->filename);

					if ($extend->key == 'avatar') {

						$storage = PATH . 'content' . DS . 'avatar' . DS . $extend->value->filename;

						$backup = PATH . 'content' . DS . 'backup' . DS . $extend->value->filename;

						$file = $extend->value->filename;

						if (file_exists($storage)) {
							$value = $file;
						} else {
							if (file_exists($backup)) {
								if (copy($backup, $storage)) {
									$value = $file;
								}
							}
						}
					} else {
						$value = $file;
					}

				}
				break;
		}

		return $value;
	}

	public static function fields($type, $id = -1) {
		$fields = Query::table(static::table())->where('type', '=', $type)->get();

		foreach(array_keys($fields) as $index) {
			$meta = Query::table(static::table($type . '_meta'))
				->where($type, '=', $id)
				->where('extend', '=', $fields[$index]->id)
				->fetch();

			$fields[$index]->value = Json::decode($meta ? $meta->data : '{}');
		}

		return $fields;
	}

	public static function html($item) {
		switch($item->field) {
			case 'text':
				$value = isset($item->value->text) ? $item->value->text : '';
				$html = '<input class="form-control" id="extend_' . $item->key . '" name="extend[' . $item->key . ']" type="text" value="' . $value . '">';
				break;

			case 'html':
				$value = isset($item->value->html) ? $item->value->html : '';
				$html = '<textarea class="form-control" id="extend_' . $item->key . '" name="extend[' . $item->key . ']" type="text">' . $value . '</textarea>';
				break;

			case 'image':
			case 'file':
				$value = isset($item->value->filename) ? $item->value->filename : '';

				$html = '';

				if($value) {

			if ($item->key == 'avatar') {
			$html .= '<label class="control-label"><img src="' . asset('content/avatar/' . $value) . '" class="img-responsive img-thumbnail"></label>';
			} else {
			$html .= '<a href="' . asset('content/' . $value) . '" target="_blank">' . $value . '</a>';
			}
				}

				$html .= '<input class="' .  ($item->key == 'avatar' ? 'sr-only' : 'form-control') . '" id="extend_' . $item->key . '" name="extend[' . $item->key . ']" type="file">';

				if($value) {
					$html .= '<label>Remove ' . $item->label . ':</label>
					<input type="checkbox" name="extend_remove[' . $item->key . ']" value="1">';
				}

				break;

			default:
				$html = '';
		}

		return $html;
	}

	public static function paginate($page = 1, $perpage = 10) {
		$query = Query::table(static::table());

		$count = $query->count();

		$results = $query->take($perpage)->skip(($page - 1) * $perpage)->get();

		return new Paginator($results, $count, $page, $perpage, Uri::to('admin/setting/fields'));
	}

	/*
		Process field types
	*/

	public static function files() {
		// format file array
		$files = array();

		if(isset($_FILES['extend'])) {
			foreach($_FILES['extend'] as $label => $items) {
				foreach($items as $key => $value) {
					$files[$key][$label] = $value;
				}
			}
		}

		return $files;
	}

	public static function upload($file, $avatar = null) {
		$storage = PATH . 'content' . DS;
		$original = '';

		if ($avatar) {
			$storage .= 'avatar' . DS;
			$original .= $storage . 'original' . DS;

			if(!is_dir($original)) mkdir($original);
		}

		if(!is_dir($storage)) mkdir($storage);

		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		// Added rtrim to remove file extension before adding again

		if ($avatar) {
			//$filename = hash('md5', $avatar) . '.' . $ext;
			$filename = preg_replace( "/^([^@]+)(@.*)$/", "$1", $avatar) . '.' .  $ext;

			//$filename = rtrim($file['name'], '.' . $ext) . '.' . $ext;
		} else {
			$filename = slug(rtrim($file['name'], '.' . $ext)) . '.' . $ext;
		}

		$filepath = $storage . $filename;

		if(move_uploaded_file($file['tmp_name'], $filepath)) {
			if ($avatar) {
				$copy = $original . $filename;
				if (!copy($filepath, $copy)) {
					return $filepath;
				}
			}

			return $filepath;
		}

		return false;
	}

	public static function process_image($extend) {
		$file = Arr::get(static::files(), $extend->key);

		if($file and $file['error'] === UPLOAD_ERR_OK) {
			$name = basename($file['name']);
			$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

			$avatar = ($extend->key == 'avatar') ? $extend->email : null;

			if($filepath = static::upload($file, $avatar)) {
				$filename = basename($filepath);

				// resize image
				if(isset($extend->attributes->size->width) and isset($extend->attributes->size->height)) {
					$image = Image::open($filepath);

					$width = intval($extend->attributes->size->width);
					$height = intval($extend->attributes->size->height);

					// resize larger images
					if(
						($width and $height) and
						($image->width() > $width or $image->height() > $height)
					) {
						$image->resize($width, $height);

						$image->output($ext, $filepath);
					}
				}

				return Json::encode(compact('name', 'filename'));
			}
		}
	}

	public static function process_file($extend) {
		$file = Arr::get(static::files(), $extend->key);

		if($file and $file['error'] === UPLOAD_ERR_OK) {
			$name = basename($file['name']);

			if($filepath = static::upload($file)) {
				$filename = basename($filepath);

				return Json::encode(compact('name', 'filename'));
			}
		}
	}

	public static function process_text($extend) {
		$text = Input::get('extend.' . $extend->key);

		return Json::encode(compact('text'));
	}

	public static function process_html($extend) {
		$html = Input::get('extend.' . $extend->key);

		return Json::encode(compact('html'));
	}

	/*
		Save
	*/

	public static function process($type, $item, $email = null) {
		foreach(static::fields($type, $item) as $extend) {

	  if ($email) {
		$extend->email = $email;
	  }

			if($extend->attributes) {
				$extend->attributes = Json::decode($extend->attributes);
			}

			$data = call_user_func_array(array('Extend', 'process_' . $extend->field), array($extend, $item));

			// save data
			if( ! is_null($data)) {
				$table = static::table($extend->type . '_meta');
				$query = Query::table($table)
					->where('extend', '=', $extend->id)
					->where($extend->type, '=', $item);

				if($query->count()) {
					$query->update(array('data' => $data));
				}
				else {
					$query->insert(array(
						'extend' => $extend->id,
						$extend->type => $item,
						'data' => $data
					));
				}
			}

			// remove data
			if(Input::get('extend_remove.' . $extend->key)) {

				if(isset($extend->value->filename) and strlen($extend->value->filename)) {

					Query::table(static::table($extend->type . '_meta'))
						->where('extend', '=', $extend->id)
						->where($extend->type, '=', $item)->delete();

			$resource = PATH . 'content' . DS . ($extend->key == 'avatar' ? 'avatar' : '') . DS . $extend->value->filename;
					file_exists($resource) and unlink(PATH . 'content' . DS . ($extend->key == 'avatar' ? 'avatar' : '') . DS . $extend->value->filename);
				}
			}
		}
	}

}
