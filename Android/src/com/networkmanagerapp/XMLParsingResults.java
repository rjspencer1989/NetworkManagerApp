package com.networkmanagerapp;

import java.util.List;
/**
 * 
 * @author rjs07u
 * An instance of this class stores the ArrayList of XMLItems and String array of XMLItem names
 * from a parsed XML file
 */
public class XMLParsingResults {
	private List<XMLItem> item;
	private String[] names;
	
	/**
	 * Creates a new object to store the results of parsing an XML file
	 * @param names The string array containing the names of XMLItem objects
	 * @param items The ArrayList containing the actual XMLItem objects
	 */
	public XMLParsingResults(String[] names, List<XMLItem> items){
		setItem(items);
		setNames(names);
	}
	
	/**
	 * Get a List of all the XMLItems 
	 * @return the List of XMLItems
	 */
	public List<XMLItem> getItem() {
		return item;
	}
	
	/**
	 * Set the List of XMLItems. Called from the constructor. There should be no need to call this directly.
	 * @param item The List of XMLItem objects
	 */
	public void setItem(List<XMLItem> item) {
		this.item = item;
	}
	
	/**
	 * Get a string array of names from XMLItem objects for use in ListViews.
	 * @return the String array containing names.
	 */
	public String[] getNames() {
		return names;
	}
	
	/**
	 * Sets the string array containing names of XMLItem objects. Called from the constructor. 
	 * There should be no need to call this method directly.
	 * @param names The string array containing names of XMLItems
	 */
	public void setNames(String[] names) {
		this.names = names;
	}	
}
