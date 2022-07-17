<?php

//Same as regular form dropdown but with H on display values
function form_label($label_text = '', $id = '', $attributes = array(),$escape = TRUE)
{

	$label = '<label';

	if ($id !== '')
	{
		$label .= ' for="'.$id.'"';
	}

	if (is_array($attributes) && count($attributes) > 0)
	{
		foreach ($attributes as $key => $val)
		{
			$label .= ' '.$key.'="'.$val.'"';
		}
	}

	if ($escape)
	{
		$label_text = H($label_text);
	}
	
	return $label.'>'.$label_text.'</label>';
}
//Same as regular form dropdown but with H on display values
function form_dropdown($data = '', $options = array(), $selected = array(), $extra = '')
{
	$defaults = array();

	if (is_array($data))
	{
		if (isset($data['selected']))
		{
			$selected = $data['selected'];
			unset($data['selected']); // select tags don't have a selected attribute
		}

		if (isset($data['options']))
		{
			$options = $data['options'];
			unset($data['options']); // select tags don't use an options attribute
		}
	}
	else
	{
		$defaults = array('name' => $data);
	}

	is_array($selected) OR $selected = array($selected);
	is_array($options) OR $options = array($options);

	// If no selected state was submitted we will attempt to set it automatically
	if (empty($selected))
	{
		if (is_array($data))
		{
			if (isset($data['name'], $_POST[$data['name']]))
			{
				$selected = array($_POST[$data['name']]);
			}
		}
		elseif (isset($_POST[$data]))
		{
			$selected = array($_POST[$data]);
		}
	}

	$extra = _attributes_to_string($extra);

	$multiple = (count($selected) > 1 && stripos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

	$form = '<select '.rtrim(_parse_form_attributes($data, $defaults)).$extra.$multiple.">\n";

	foreach ($options as $key => $val)
	{
		$key = (string) $key;

		if (is_array($val))
		{
			if (empty($val))
			{
				continue;
			}

			$form .= '<optgroup label="'.$key."\">\n";

			foreach ($val as $optgroup_key => $optgroup_val)
			{
				$sel = in_array($optgroup_key, $selected) ? ' selected="selected"' : '';
				$form .= '<option value="'.H($optgroup_key).'"'.$sel.'>'
					.(string) H($optgroup_val)."</option>\n";
			}

			$form .= "</optgroup>\n";
		}
		else
		{
			$form .= '<option value="'.H($key).'"'
				.(in_array($key, $selected) ? ' selected="selected"' : '').'>'
				.(string) H($val)."</option>\n";
		}
	}

	return $form."</select>\n";
}