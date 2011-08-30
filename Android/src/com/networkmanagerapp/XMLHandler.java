/**
 * Class to parse XML data files using SAX XML Parser
 * This is a XML handler to specify what to do for each component of an XML Document
 */

package com.networkmanagerapp;

import java.util.ArrayList;
import java.util.List;

import org.xml.sax.Attributes;
import org.xml.sax.SAXException;
import org.xml.sax.helpers.DefaultHandler;
/**
 * This class is a subclass of DefaultHandler that can parse XML files generated by the router
 * @author rjs07u
 *
 */
class XMLHandler extends DefaultHandler{
	private XMLItem anItem; //represents an item that will be displayed in a ListView
	private String elementContent; //content of xml <item>content</item> structure
	private List<XMLItem> itemData; //list of items to be displayed in ListView
	
	public void setItemData(final List<XMLItem> itemData) {
		this.itemData = itemData;
	}
	public List<XMLItem> getItemData() {
		return itemData;
	}
	public String getElementContent() {
		return elementContent;
	}
	public void setElementContent(final String elementContent) {
		this.elementContent = elementContent;
	}
	
	public XMLItem getAnItem() {
		return anItem;
	}
	public void setAnItem(final XMLItem anItem) {
		this.anItem = anItem;
	}
	/**
	 * Delegate method called when <item>content</item> structures are found in an XML doc
	 * @param char[] characters - a character array containing the content part of the xml element
	 * @param int start - start index for the number of characters in array
	 * @param int length - length of content
	 * @throws SAXException
	 */
	@Override
	public void characters(final char[] characters, final int start, final int length)
			throws SAXException {
		final char[] contents = characters;
		elementContent += new String(contents, start, length); 
	}
	/**
	 * Delegate method called when end element is found
	 * @throws SAXException
	 * @param String uri - the location in the document
	 * @param String localName - the element name
	 * @param String qName - The unique name in the document 
	 */
	@Override
	public void endElement(final String uri, final String localName, final String qName)
			throws SAXException {
		if ("item".equals(localName)) {
			//item content populated add to vector
			itemData.add(anItem);
		} else if ("root".equals(localName)) {
			//end of document, nothing to do
			return;
		}else{
			//get arraylist for current item and set the key with the current tag to element content
			anItem.getItemData().put(localName, elementContent);
		}
	}

	/**
	 * Delegate method called at the start of the Document
	 * @throws SAXException
	 */
	@Override
	public void startDocument() throws SAXException {
		itemData = new ArrayList<XMLItem>();
	}

	/**
	 * Delegate method called for each open tag
	 * @param String uri Location in the document
	 * @param String localName Element Name
	 * @param String qName unique name
	 * @param Attributes attributes XML Attributes
	 * @throws SAXException
	 */
	@Override
	public void startElement(final String uri, final String localName, final String qName,
			final Attributes attributes) throws SAXException {
		if ("item".equals(localName)) {
			anItem = new XMLItem();
		} else if ("root".equals(localName)) {
			return;
		} else {
			elementContent = "";
		}
	}
}