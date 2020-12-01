# Setting custom colors on picker

In order to set custom colors to show in the color picker, set following configuration:

```
$bsgVisualEditorConnectorColorPickerColors = [
    [
        'class': 'color-blue',
        'name': 'Blue'
    ],
    [
        'code': '#04B431',
        'name': 'Green'
    ]
];
```

Set array of color configs, each config must have "name" property, which is human-readable
color name for the tooltip. It also must contain at least
one of these props: "code" or "class".
If code is specified, annotated snipped will look like:

`
<span style="color: #04B431;">snippet</span>
`

while if "class" is specified it will look like:

`
<span class="class-name">snippet</span>
`

Class specified must contain "color" property.